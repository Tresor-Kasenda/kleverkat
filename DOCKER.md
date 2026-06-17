# Dockerisation de kleverkat-web — guide pas à pas

Ce document explique **comment** l'application est conteneurisée et surtout **pourquoi**
chaque choix a été fait. À chaque ajout, la question posée était : *« est-ce un bon choix,
sinon comment l'optimiser ? »*. Les réponses sont consignées ici.

---

## 1. Vue d'ensemble

L'application tourne dans plusieurs conteneurs qui collaborent :

```
                         ┌─────────────────────────────┐
   navigateur  ─────────▶│  app   (FrankenPHP + Laravel)│  :8000 (dev) / :80 (prod)
                         └──────────────┬──────────────┘
                                        │
                ┌───────────────────────┼───────────────────────┐
                ▼                       ▼                        ▼
        ┌──────────────┐        ┌──────────────┐         ┌──────────────┐
        │   mysql 8.4  │        │  redis 7     │         │ queue worker │
        │  (données)   │        │ cache/queue/ │         │  (jobs)      │
        └──────────────┘        │  session     │         └──────────────┘
                                └──────────────┘
   En dev uniquement :  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────────┐
                        │ vite (HMR)   │  │ mailpit      │  │ adminer      │  │ redis-commander│
                        │  :5173       │  │ mail :8025   │  │ MySQL :8080  │  │ Redis :8082    │
                        └──────────────┘  └──────────────┘  └──────────────┘  └────────────────┘
   En prod uniquement : un conteneur "scheduler" (php artisan schedule:work)
```

- **dev** (`docker-compose.yml`) : le code source est *monté* depuis votre machine, Vite
  recharge les assets à chaud, aucune optimisation (pour itérer vite).
- **prod** (`docker-compose.prod.yml`) : une **image figée et optimisée** est construite,
  sans Composer ni Node, exécutée par un utilisateur non-root.

---

## 2. Pourquoi ces choix (le journal des décisions)

| Décision | Choix retenu | Pourquoi — et l'alternative écartée |
|---|---|---|
| **Serveur web** | **FrankenPHP** | Une seule image (PHP + serveur web Caddy intégré), HTTP/2 et HTTP/3, TLS automatique. Alternative *Nginx + PHP-FPM + supervisor* écartée : 3 pièces à configurer et synchroniser pour le même résultat. |
| **Octane (worker mode)** | **Non activé** | Garderait l'app en mémoire entre les requêtes (gros gain de perf) mais expose à des **fuites d'état** avec Livewire/Filament si le code n'a pas été pensé pour. On reste en mode « classique » (sûr) ; activable plus tard (§7). |
| **Build multi-stage** | **3 stages** (vendor → frontend → runtime) | L'image finale ne contient **ni Composer, ni Node, ni npm** → plus petite, surface d'attaque réduite. Alternative *image unique* écartée : elle embarque des outils de build inutiles en production. |
| **Ordre composer → npm** | **Imposé** | Le CSS du front *importe* des fichiers de `vendor/` (`vendor/livewire/flux/dist/flux.css`, `vendor/filament/.../theme.css`). Le stage Node reçoit donc une copie de `vendor/`. |
| **Base de données** | **MySQL 8.4** | Demandé. La base SQLite locale existante n'est **pas** reprise automatiquement : on repart de migrations fraîches (voir §4). |
| **Cache / Queue / Session** | **Redis 7** | Demandé. Plus rapide que le stockage en base ; un seul service couvre les 3 usages. |
| **Extensions PHP** | `pdo_mysql redis intl gd zip bcmath pcntl opcache exif` | `pdo_mysql`+`redis` obligatoires pour la stack ; `gd`+`exif` pour les images Filament ; `opcache` = perf prod ; `intl`/`bcmath`/`zip` couramment requis ; `pcntl` pour les workers de queue. On n'installe **rien de plus** que nécessaire. |
| **Utilisateur runtime** | **non-root** (`www-data`) en prod | Bonne pratique de sécurité conteneur. FrankenPHP peut quand même écouter sur le port 80 grâce à une capacité posée sur son binaire. |
| **OPcache** | **activé en prod, désactivé en dev** | En prod le code est immuable dans l'image → `validate_timestamps=0` (zéro `stat` disque) + JIT. En dev on le désactive pour voir les changements immédiatement. |
| **Caches Laravel** | construits **au démarrage** du conteneur prod (pas au build) | `config:cache` fige les variables d'environnement. En les générant au démarrage, l'image reste réutilisable d'un environnement à l'autre. En dev : **aucun cache** (sinon `.env` figé). |
| **Migrations** | **opt-in** (`RUN_MIGRATIONS=true`) | Si on passe à plusieurs répliques, une seule (le conteneur web) migre → pas de course/conflit. |
| **`.dockerignore`** | strict | Exclut `vendor/`, `node_modules/`, `public/build`, `.env`, `database.sqlite` → build plus rapide et **aucun secret** copié dans l'image. |
| **Service mail (dev)** | **Mailpit** | Capture tous les e-mails sortants + UI web ; rien ne part vraiment. Standard Laravel moderne (remplace MailHog), léger et multi-arch. **Dev uniquement.** |
| **UI MySQL (dev)** | **Adminer** | Un seul binaire PHP, image minuscule, multi-arch, se connecte tout seul au service `mysql`. Alternative *phpMyAdmin* écartée : bien plus lourde pour le même besoin. **Dev uniquement.** |
| **UI Redis (dev)** | **Redis Commander** | Se connecte automatiquement à `redis` (meilleure UX). Image **amd64 seulement** → on épingle `platform: linux/amd64` (émulée sur Mac Apple Silicon). Alternative *RedisInsight* : multi-arch mais connexion à ajouter à la main. **Dev uniquement.** |
| **Outils non ajoutés** | Xdebug, Octane | Volontairement absents par défaut (principe : ne rien ajouter sans besoin). Voir §7 pour les activer. |
| **Dépendances du projet** | **inchangées** | Aucune modification de `composer.json` / `package.json` : on n'a ajouté que de l'infrastructure Docker. |

---

## 3. Anatomie de l'image (`Dockerfile`)

Le `Dockerfile` est découpé en **stages** ; chacun a un rôle unique.

1. **`base`** — part de `dunglas/frankenphp:1-php8.4-bookworm`, installe `curl` (pour le
   healthcheck) et les extensions PHP, puis copie le binaire `composer`. Tous les autres
   stages héritent de cette base.

2. **`vendor`** — installe les dépendances PHP **de production** :
   - on copie d'abord *seulement* `composer.json` + `composer.lock` et on lance
     `composer install --no-dev --no-autoloader` → cette couche est **mise en cache** et
     n'est reconstruite que si ces fichiers changent ;
   - on copie ensuite le code, puis on génère l'**autoloader optimisé**
     (`composer dump-autoload --optimize`) pour que les classes de `app/` y soient incluses,
     et on lance `php artisan package:discover`.

3. **`frontend`** — image `node:22`, compile les assets :
   - `npm ci` (installation reproductible depuis `package-lock.json`) ;
   - on copie `vite.config.js`, `resources/`, **et `vendor/`** (indispensable, cf. §2) ;
   - `npm run build` → produit `public/build`.

4. **`dev`** — runtime de développement : il **ne contient pas** le code (celui-ci est monté
   depuis votre machine via `docker-compose.yml`). Il embarque juste FrankenPHP, les
   extensions, `php.dev.ini` et le `Caddyfile`.

5. **`prod`** — image finale :
   - copie le code, puis remplace `vendor/` (depuis `vendor`) et `public/build`
     (depuis `frontend`) ;
   - publie les assets cœur de Filament (`php artisan filament:assets`) ;
   - donne les dossiers inscriptibles (`storage`, `bootstrap/cache`, `public`) à `www-data` ;
   - bascule sur l'utilisateur **non-root**, expose un **HEALTHCHECK** sur `/up`, et démarre
     via `entrypoint` (voir §5).

### Fichiers de configuration associés
- `docker/Caddyfile` — racine `public/`, compression, en-têtes de sécurité, logs vers stdout.
- `docker/php/php.prod.ini` / `php.dev.ini` — réglages PHP par environnement.
- `docker/entrypoint.sh` — préparation de l'app au démarrage (prod).

---

## 4. Démarrer en développement

Prérequis : Docker Desktop.

```bash
# 1. Préparer l'environnement
cp .env.docker.example .env

# 2. Construire et lancer (app + mysql + redis + vite + queue)
docker compose up -d --build

# 3. Générer la clé d'application
docker compose exec app php artisan key:generate

# 4. Créer le schéma + données de démo (MySQL est vide au départ)
docker compose exec app php artisan migrate --seed

# 5. Publier les assets de Filament (une seule fois)
docker compose exec app php artisan filament:assets
```

Accès :
- Application : <http://localhost:8000>
- Admin Filament : <http://localhost:8000/admin>
- Serveur Vite (HMR) : <http://localhost:5173>
- **Mailpit** (e-mails capturés) : <http://localhost:8025>
- **Adminer** (base MySQL) : <http://localhost:8080> — système `MySQL`, serveur `mysql`, utilisateur/mot de passe = `DB_USERNAME`/`DB_PASSWORD` du `.env`, base `kleverkat`
- **Redis Commander** (clés Redis) : <http://localhost:8082> — déjà connecté au service `redis`

> Tous ces outils sont **dev uniquement** : ils ne figurent pas dans `docker-compose.prod.yml`.

> **Note SQLite → MySQL** : les données de `database/database.sqlite` ne sont pas migrées
> automatiquement. Les seeders (`DatabaseSeeder`, `ProductSeeder`, `SectorSeeder`) recréent un
> jeu de données. Pour importer l'ancien contenu, il faudrait un export/import manuel.

---

## 5. Builder et lancer en production

```bash
# 1. Préparer un .env de production
cp .env.docker.example .env
#    puis éditer :  APP_ENV=production  APP_DEBUG=false
#                   APP_URL=https://votre-domaine   DB_PASSWORD=<fort>
#    et générer la clé :
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show
#    (copier la valeur dans APP_KEY)

# 2. Construire et démarrer (app + queue + scheduler + mysql + redis)
docker compose -f docker-compose.prod.yml up -d --build
```

Au démarrage, `docker/entrypoint.sh` :
1. crée le lien `storage:link` ;
2. (re)construit les caches `config/route/view/event` **contre l'environnement réel** ;
3. exécute `php artisan filament:optimize` ;
4. lance les **migrations** (car `RUN_MIGRATIONS=true` sur le service `app` uniquement).

Le service `app` expose un **HEALTHCHECK** sur `/up` (route santé native de Laravel).

---

## 6. Tâches courantes

```bash
# Logs
docker compose logs -f app

# Une commande artisan quelconque
docker compose exec app php artisan about

# Tinker
docker compose exec app php artisan tinker

# Lancer la suite de tests dans le conteneur
docker compose exec app php artisan test --compact

# Reconstruire les assets (prod) après un changement de front
docker compose -f docker-compose.prod.yml build app

# Tout arrêter (les volumes db-data/redis-data sont conservés)
docker compose down
```

---

## 7. Optimisations futures (volontairement non activées)

Chacune a été *considérée* puis écartée **pour l'instant** — voici quand l'activer.

- **Octane worker mode (FrankenPHP)** — gros gain de débit en gardant l'app en mémoire.
  À activer une fois validé qu'aucun état ne fuit entre requêtes (audit Livewire/Filament).
  Nécessite `composer require laravel/octane` puis un CMD `octane:frankenphp`.
- **Xdebug** (débogage pas-à-pas) — à ajouter dans le stage `dev` uniquement ; coûte en perf,
  donc activé seulement à la demande.
- **Cache de build BuildKit** (`--mount=type=cache` pour Composer/npm) — accélère les rebuilds
  en CI ; non ajouté pour garder le `Dockerfile` lisible et portable.
- **Image distroless / Alpine** — plus petite, mais FrankenPHP officiel est Debian ; le gain ne
  justifie pas (encore) la complexité.

---

## 8. Récapitulatif des fichiers ajoutés

| Fichier | Rôle |
|---|---|
| `Dockerfile` | Build multi-stage (base / vendor / frontend / dev / prod). |
| `.dockerignore` | Exclut deps, caches, secrets du contexte de build. |
| `docker-compose.yml` | Stack de **développement** (source montée + Vite HMR + Mailpit + Adminer + Redis Commander). |
| `docker-compose.prod.yml` | Stack de **production** (image figée + worker + scheduler). |
| `.env.docker.example` | Variables d'environnement adaptées aux conteneurs. |
| `docker/Caddyfile` | Configuration du serveur FrankenPHP. |
| `docker/entrypoint.sh` | Préparation de l'app au démarrage (prod). |
| `docker/php/php.prod.ini` | Réglages PHP de production (OPcache + JIT). |
| `docker/php/php.dev.ini` | Réglages PHP de développement (erreurs visibles). |
| `vite.config.js` | *(modifié)* HMR compatible Docker, **gaté par `DOCKER=true`** (comportement hors-Docker inchangé). |
