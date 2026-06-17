# Architecture & Design Système

## 1. Vue d'Ensemble

```
┌─────────────────────────────────────────────────┐
│                  FRONTEND PUBLIC                 │
│  Livewire v4 + Flux UI + Tailwind CSS v4        │
│  Pages: Accueil, Comparaison, Résultats,         │
│         Contact, Légal                           │
└───────────────────────┬─────────────────────────┘
                        │ HTTP
┌───────────────────────▼─────────────────────────┐
│              LARAVEL 13 APPLICATION              │
│                                                   │
│  ┌─────────────┐  ┌──────────┐  ┌────────────┐  │
│  │ Routes Web   │  │ Routes   │  │ Routes API │  │
│  │ (Public)     │  │ Admin    │  │ (Partner)  │  │
│  └──────┬──────┘  └────┬─────┘  └─────┬──────┘  │
│         │              │              │          │
│  ┌──────▼──────────────▼──────────────▼──────┐   │
│  │          MIDDLEWARE                        │   │
│  │  auth, role, session, throttle, etc.       │   │
│  └───────────────────┬───────────────────────┘   │
│                      │                           │
│  ┌───────────────────▼───────────────────────┐   │
│  │          CONTROLLERS / LIVEWIRE           │   │
│  │  ComparaisonController, ProductController,│   │
│  │  LeadController, CompanyController, etc.  │   │
│  └───────────────────┬───────────────────────┘   │
│                      │                           │
│  ┌───────────────────▼───────────────────────┐   │
│  │          SERVICES LAYER                    │   │
│  │  ScoringService, LeadService,             │   │
│  │  ComparisonService, ProductService        │   │
│  └───────────────────┬───────────────────────┘   │
│                      │                           │
│  ┌───────────────────▼───────────────────────┐   │
│  │            ELOQUENT MODELS                │   │
│  │  Sector, Company, Product, Questionnaire,  │   │
│  │  Question, Lead, ScoringCoefficient, etc. │   │
│  └───────────────────┬───────────────────────┘   │
│                      │                           │
└───────────────────────┼─────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────┐
│              DATABASE (SQLite / MySQL)           │
└─────────────────────────────────────────────────┘
```

## 2. Stack Technique

| Couche | Technologie | Justification |
|--------|------------|---------------|
| Backend | Laravel 13 | Framework mature, écosystème riche |
| Admin | Filament v5 | CRUD rapide, beau UI, permission intégrée |
| Frontend | Livewire v4 + Flux UI | Composants réactifs sans JS lourd |
| CSS | Tailwind CSS v4 | Utility-first, rapide à développer |
| Auth | Laravel Fortify | Authentication headless, flexible |
| Base | SQLite → MySQL | Évolution possible |
| Tests | Pest v4 | Moderne, lisible |

## 3. Modules Applicatifs

### 3.1. Module Comparaison (Public)
- **Livewire Component** `CompareWizard` — questionnaire multi-étapes
- Étape 1 : Choix de la catégorie de produit
- Étape 2 : Remplissage du questionnaire dynamique
- Étape 3 : Affichage des résultats classés
- **Service** `ComparisonService` — logique de calcul des scores

### 3.2. Module Administration (Filament)
- **Resources Filament :** SectorResource, CompanyResource, ProductCategoryResource, ProductResource, QuestionnaireResource, QuestionResource, LeadResource
- **Widgets :** Dashboard stats, leads récents, produits populaires

### 3.3. Module Partenaire (Livewire)
- **Dashboard Partner** — Vue d'ensemble des leads et performances
- **Product Manager** — Édition des produits (limité à ses propres produits)
- **Scoring Manager** — Ajustement des coefficients de scoring
- **Leads View** — Consultation des leads reçus

## 4. Sécurité

- Rôles : `admin`, `partner`
- Filament : Gates et Policies pour chaque ressource
- Partenaire : middleware `role:partner` + vérification `company_id` sur chaque action
- Rate limiting sur le formulaire de comparaison (anti-bot)
- Validation CSRF sur toutes les requêtes
- Sanitization des entrées utilisateur
