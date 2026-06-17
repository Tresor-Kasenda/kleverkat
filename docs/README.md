# KleverKat — Documentation

Bienvenue dans la documentation de la plateforme de comparaison d'assurances KleverKat.

## Structure

| Fichier                             | Description                                                                   |
|-------------------------------------|-------------------------------------------------------------------------------|
| `01-PRD.md`                         | Product Requirements Document — vision, objectifs, périmètre                  |
| `02-Modelling.md`                   | Modélisation de données — entités, relations, migrations                      |
| `03-Architecture.md`                | Architecture système — stack, modules, sécurité                               |
| `04-User-Roles.md`                  | Rôles et permissions — admin, partenaire, visiteurs                           |
| `05-Use-Cases.md`                   | Cas d'utilisation détaillés — 9 scénarios complets                            |
| `06-Flow-Scenarios.md`              | Scénarios de flux — parcours utilisateur, scoring                             |
| `07-Frontend-Structure.md`          | Structure frontend — pages, composants, navigation                            |
| `08-Tech-Specs.md`                  | Spécifications techniques — fichiers, commandes, tests                        |
| `09-Dynamic-Form-Implementation.md` | Implémentation du formulaire dynamique — architecture, Livewire, Flux, tests  |
| `10-Complete-LesFurets-Modeling.md` | Modélisation avancée LesFurets — base de données, scoring, leads, facturation |
| `13-Modelisation-Cible-et-Plan-Implementation.md` | Revue de la modelisation cible — ecarts du projet, schema recommande, plan MVP |

## Document de reference recommande

Si tu dois repartir proprement depuis l'etat actuel du depot, commence par :

- `13-Modelisation-Cible-et-Plan-Implementation.md`

## Prochaines Étapes

1. Stabiliser la modelisation cible (`product_types`, `offers`, `offer_rules`, `comparison_sessions`)
2. Aligner les roles sur le socle `teams` deja present
3. Creer les migrations et modeles du domaine
4. Implementer l'admin Filament
5. Implementer le wizard public Livewire
6. Implementer l'espace partenaire
7. Ajouter le moteur de comparaison et les tests

## Références

- **Stack :** Laravel 13 / Livewire 4 / Filament 5 / Flux UI / Tailwind 4
- **Inspiration :** [LesFurets.com](https://www.lesfurets.com/)
