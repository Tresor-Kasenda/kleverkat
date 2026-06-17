# Scénarios de Flux Complets

## 1. Parcours Utilisateur Complet (Visiteur → Lead)

```
ACCUEIL
  │
  ├── Choisit une catégorie (ex: Assurance Auto)
  │
  ▼
QUESTIONNAIRE DYNAMIQUE
  │
  ├── Étape 1 : Informations personnelles
  │   ├── Nom, Prénom
  │   ├── Email
  │   ├── Téléphone
  │   ├── Code postal
  │   └── Âge / Date de naissance
  │
  ├── Étape 2 : Informations spécifiques
  │   ├── Type de véhicule
  │   ├── Usage (personnel/professionnel)
  │   ├── Kilométrage annuel
  │   ├── Bonus/Malus
  │   └── Garanties souhaitées
  │
  └── Soumission
  │
  ▼
CALCUL DU SCORE (Backend)
  │
  ├── 1. Récupérer tous les produits actifs de la catégorie
  ├── 2. Pour chaque produit :
  │   ├── Récupérer les coefficients de scoring
  │   ├── Si le partenaire a défini un coefficient : l'utiliser
  │   └── Sinon : utiliser le coefficient par défaut
  ├── 3. Calculer le score pondéré
  ├── 4. Trier les produits par score décroissant
  └── 5. Créer le lead en base
  │
  ▼
AFFICHAGE DES RÉSULTATS
  │
  ├── Classement n°1 : Meilleur rapport score/prix
  ├── Classement n°2 : ...
  ├── Classement n°3 : ...
  │
  ├── Pour chaque résultat :
  │   ├── Logo entreprise
  │   ├── Nom du produit
  │   ├── Prix estimé (min-max)
  │   ├── Score d'adéquation
  │   ├── Garanties mises en avant
  │   └── Boutons : "Voir détails" / "Demander un devis"
  │
  └── Actions possibles :
      ├── Voir les détails complets du produit
      ├── Demander un devis → notification partenaire
      └── Être redirigé vers le site partenaire
```

## 2. Parcours Partenaire (Connexion → Actions)

```
CONNEXION PARTENAIRE
  │
  ▼
DASHBOARD
  │
  ├── Widget : Leads du jour
  ├── Widget : Évolution mensuelle
  ├── Widget : Top produits
  └── Menu latéral :
      │
      ├── Mes Produits
      │   ├── Liste des produits
      │   ├── Modifier un produit
      │   │   ├── Informations générales
      │   │   ├── Détails/garanties
      │   │   └── Activer/Désactiver
      │   └── Voir l'aperçu public
      │
      ├── Scoring
      │   ├── Liste des questions
      │   ├── Ajuster les coefficients
      │   └── Sauvegarder
      │
      ├── Leads
      │   ├── Liste avec filtres (date, statut, produit)
      │   ├── Détail du lead (réponses, score)
      │   └── Changer statut
      │
      └── Mon Entreprise
          ├── Modifier les infos
          ├── Changer le logo
          └── Mettre à jour le site web
```

## 3. Parcours Admin (Création Complète)

```
ADMIN CONNECTÉ (FILAMENT)
  │
  ├── 1. Créer un secteur (ex: "Assurance")
  │
  ├── 2. Créer une catégorie (ex: "Auto")
  │
  ├── 3. Créer une entreprise partenaire
  │   └── Compte utilisateur généré automatiquement
  │
  ├── 4. Créer un produit (lié à l'entreprise + catégorie)
  │
  ├── 5. Créer un questionnaire (lié à la catégorie)
  │   ├── Ajouter des questions avec sections
  │   └── Configurer les types et options
  │
  ├── 6. Configurer les coefficients de scoring par défaut
  │
  └── 7. Activer le produit → visible sur le site public
```

## 4. Flux de Scoring (Détail du Calcul)

```php
// Logique simplifiée du ScoringService

function calculateScore(array $answers, Product $product): float
{
    $totalScore = 0;
    $totalWeight = 0;

    foreach ($answers as $questionId => $answer) {
        $question = Question::find($questionId);
        
        // Récupérer le coefficient (partenaire ou défaut)
        $coefficient = ScoringCoefficient::where('question_id', $questionId)
            ->where('company_id', $product->company_id)
            ->first()
            ?->coefficient 
            ?? ScoringCoefficient::where('question_id', $questionId)
                ->whereNull('company_id')
                ->first()
                ?->coefficient 
            ?? 1.0;

        // Score de base pour cette réponse
        $answerScore = $this->evaluateAnswer($question, $answer);
        
        // Pondération
        $totalScore += $answerScore * $coefficient;
        $totalWeight += $coefficient;
    }

    // Score final normalisé (0-100)
    return $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;
}
```
