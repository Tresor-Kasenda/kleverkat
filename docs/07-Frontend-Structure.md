# Structure Frontend (Pages & Composants)

## 1. Pages Publiques

| Route | Page | Description |
|-------|------|-------------|
| `/` | Accueil | Landing page, choix de catégorie, recherche |
| `/comparer/{category}` | Questionnaire | Formulaire dynamique multi-étapes |
| `/resultats/{lead}` | Résultats | Classement des produits avec scores |
| `/produit/{product}` | Détail Produit | Fiche détaillée d'un produit |
| `/entreprise/{company}` | Profil Entreprise | Page partenaire publique |
| `/comment-ca-marche` | Comment ça marche | Page explicative du processus |
| `/contact` | Contact | Formulaire de contact |
| `/mentions-legales` | Mentions | Mentions légales et CGU |

## 2. Pages Partenaire (Espace dédié)

| Route | Page | Description |
|-------|------|-------------|
| `/partner/dashboard` | Dashboard | Vue d'ensemble, KPIs, graphiques |
| `/partner/products` | Mes Produits | Liste des produits du partenaire |
| `/partner/products/{product}/edit` | Modifier Produit | Formulaire d'édition |
| `/partner/scoring` | Scoring | Gestion des coefficients |
| `/partner/leads` | Leads | Liste des leads reçus |
| `/partner/leads/{lead}` | Détail Lead | Réponses, score, actions |
| `/partner/profile` | Mon Entreprise | Modification des infos |

## 3. Composants Livewire (Public)

```
Components/
└── CompareWizard/            # Assistant de comparaison multi-étapes
    ├── index.blade.php        # Conteneur principal
    ├── StepCategory.blade.php # Étape 1 : Choix de la catégorie
    ├── StepQuestionnaire.php  # Étape 2 : Questions dynamiques
    └── StepResults.blade.php  # Étape 3 : Résultats classés

Components/
└── ProductCard.blade.php     # Carte produit dans les résultats
Components/
└── ProductDetails.blade.php  # Détails complets d'un produit
Components/
└── CategoryGrid.blade.php    # Grille des catégories (accueil)
Components/
└── SearchBar.blade.php       # Barre de recherche
Components/
└── CompanyCard.blade.php     # Carte entreprise partenaire
```

## 4. Composants Partenaire (Livewire)

```
Partner/
├── Dashboard.blade.php        # KPIs et statistiques
├── ProductList.blade.php      # Tableau des produits
├── ProductEdit.blade.php      # Formulaire d'édition produit
├── ScoringManager.blade.php   # Gestion des coefficients
├── LeadList.blade.php         # Tableau des leads avec filtres
├── LeadDetail.blade.php       # Détail d'un lead
└── CompanyProfile.blade.php   # Édition profil entreprise
```

## 5. Mise en Page (Layout)

```
resources/views/
├── layouts/
│   └── app.blade.php          # Layout principal public
│       ├── navbar (logo, menu, recherche)
│       ├── slot (contenu)
│       └── footer
│
├── layouts/
│   └── partner.blade.php      # Layout espace partenaire
│       ├── sidebar (navigation)
│       ├── topbar (profil, notifications)
│       └── slot (contenu)
│
└── components/
    ├── navbar.blade.php
    ├── footer.blade.php
    ├── partner-sidebar.blade.php
    └── ...
```

## 6. Flux de Navigation (Public)

```
Accueil
  │
  ├── Clique sur "Comparer" → /comparer/{category}
  │   ├── Remplit le questionnaire
  │   └── Submit → /resultats/{lead}
  │       ├── Clique sur un produit → /produit/{product}
  │       └── Demande devis → redirection ou notification
  │
  ├── Clique sur "Voir les partenaires" → liste des entreprises
  │   └── Clique sur une entreprise → /entreprise/{company}
  │
  └── Clique sur "Comment ça marche" → page explicative
```
