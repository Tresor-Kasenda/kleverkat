# PRD — Plateforme de Comparaison d'Assurances

## 1. Vision & Objectifs

**Vision :** Devenir la référence en France pour la comparaison d'assurances et de produits financiers, à l'instar de LesFurets.com.

**Objectifs métier :**
- Permettre aux utilisateurs de comparer des produits d'assurance (auto, habitation, santé, prévoyance, etc.) et bancaires en quelques clics
- Offrir aux entreprises partenaires (assureurs, banques) un espace dédié pour gérer leurs produits
- Générer des leads qualifiés vers les partenaires via un système de scoring et de questionnaires dynamiques

## 2. Périmètre Fonctionnel

### 2.1. Portail Public (Visiteurs)
- Comparaison multi-produits (assurance auto, habitation, santé, prévoyance, banque)
- Questionnaire dynamique adapté à chaque catégorie de produit
- Affichage des résultats avec classement par prix, garanties, et score personnalisé
- Redirection vers le partenaire ou demande de devis

### 2.2. Back-Office Administrateur (Filament)
- Gestion des entreprises partenaires (CRUD)
- Gestion des secteurs d'activité (assurance, banque, mutuelle, etc.)
- Gestion des catégories et sous-catégories de produits
- Gestion des produits d'assurance
- Gestion des questionnaires et des questions
- Gestion des coefficients de scoring
- Gestion des utilisateurs et rôles

### 2.3. Espace Partenaire (Entreprises)
- Connexion sécurisée
- Tableau de bord : statistiques, leads reçus
- Modification des informations de l'entreprise
- Gestion des produits associés (modification des détails)
- Ajustement des coefficients de scoring des questionnaires
- Consultation des leads générés

## 3. Types de Produits (exemples)

| Secteur | Catégorie | Exemples |
|----------|-----------|----------|
| Assurance | Auto | Tiers, Tous Risques |
| Assurance | Habitation | Propriétaire, Locataire |
| Assurance | Santé | Individuelle, Familiale |
| Assurance | Prévoyance | Décès, Invalidité |
| Banque | Comptes | Courant, Épargne |
| Banque | Crédits | Immobilier, Conso |
| Banque | Placements | Assurance Vie, PEA |

## 4. Contraintes Techniques

- Laravel 13 / PHP 8.4
- Filament v5 pour l'administration
- Livewire v4 + Flux UI pour le frontend public
- Base SQLite (évolution possible vers MySQL/PostgreSQL)
- Fortify pour l'authentification
- Responsive Design (Tailwind CSS v4)
