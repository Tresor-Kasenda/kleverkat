# Rôles et Permissions

## 1. Hierarchie des Rôles

```
┌─────────────────────────────┐
│         ADMIN               │
│  Accès complet au système   │
└──────────┬──────────────────┘
           │
┌──────────▼──────────────────┐
│        PARTENAIRE           │
│  Accès limité à ses données │
└─────────────────────────────┘
```

## 2. Rôle Admin (Filament)

L'administrateur a accès à toutes les resources Filament :

| Resource | Voir | Créer | Modifier | Supprimer |
|----------|------|-------|----------|-----------|
| Secteurs | ✅ | ✅ | ✅ | ✅ |
| Catégories | ✅ | ✅ | ✅ | ✅ |
| Entreprises | ✅ | ✅ | ✅ | ✅ |
| Produits | ✅ | ✅ | ✅ | ✅ |
| Détails Produits | ✅ | ✅ | ✅ | ✅ |
| Questionnaires | ✅ | ✅ | ✅ | ✅ |
| Questions | ✅ | ✅ | ✅ | ✅ |
| Coefficients Scoring | ✅ | ✅ | ✅ | ✅ |
| Leads | ✅ | ✅ | ❌ | ✅ |
| Utilisateurs | ✅ | ✅ | ✅ | ✅ |

## 3. Rôle Partenaire (Espace dédié)

Le partenaire est un utilisateur standard (non Filament) avec un espace dédié.

### Périmètre :
- Accès à son **propre dashboard**
- **Entreprise** : Consultation et modification des informations de son entreprise uniquement
- **Produits** :
  - Voir la liste de ses propres produits
  - Modifier les champs : description, prix, mise en avant, détails/garanties
  - Ne **peut pas** créer ou supprimer des produits
- **Scoring** :
  - Voir et modifier les coefficients de scoring pour les questions liées à ses catégories de produits
  - Peut surcharger le coefficient par défaut avec une valeur propre à son entreprise
- **Leads** :
  - Voir les leads générés pour ses produits
  - Modifier le statut des leads (nouveau → contacté → qualifié → converti/perdu)
  - Ne peut pas supprimer des leads

## 4. Rôles Futures (non implémentés dans cette phase)

- **Modérateur** : Validation des nouveaux produits partenaires
- **Super Admin** : Gestion des administrateurs, logs système
- **Visiteur** : Utilisateur non connecté qui compare des produits

## 5. Implémentation (Policies)

```php
// Exemple de base pour les policies
class ProductPolicy
{
    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->company_id === $product->company_id;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->company_id === $product->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
```
