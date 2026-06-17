# Cas d'Utilisation Détaillés

## UC-01 : Création d'un Secteur d'Activité

| Champ | Valeur |
|-------|--------|
| **Acteur** | Admin |
| **Précondition** | Admin connecté à Filament |
| **Postcondition** | Un nouveau secteur est créé |

**Scénario nominal :**
1. L'admin navigue dans "Secteurs" → "Créer"
2. Saisit le nom (ex: "Assurance", "Banque")
3. Le slug est généré automatiquement
4. Ajoute une description optionnelle
5. Soumet le formulaire
6. Le secteur est créé et apparaît dans la liste

---

## UC-02 : Création d'une Entreprise Partenaire

| Champ | Valeur |
|-------|--------|
| **Acteur** | Admin |
| **Précondition** | Secteur existe |
| **Postcondition** | Entreprise créée + compte utilisateur partenaire créé |

**Scénario nominal :**
1. Admin navigue dans "Entreprises" → "Créer"
2. Remplit les informations : nom, site web, email, téléphone
3. Sélectionne le secteur d'activité
4. Upload du logo
5. Active/désactive l'entreprise
6. Un compte utilisateur partenaire est automatiquement créé
7. L'entreprise reçoit un email avec ses identifiants de connexion

---

## UC-03 : Création d'un Produit d'Assurance

| Champ | Valeur |
|-------|--------|
| **Acteur** | Admin |
| **Précondition** | Entreprise partenaire et catégorie existent |
| **Postcondition** | Produit créé et visible sur le site |

**Scénario nominal :**
1. Admin navigue dans "Produits" → "Créer"
2. Sélectionne l'entreprise partenaire
3. Sélectionne la catégorie de produit
4. Remplit : nom, description, prix min/max
5. Ajoute des highlights (JSON)
6. Définit l'ordre d'affichage
7. Active ou non le produit
8. Soumet → le produit est créé

---

## UC-04 : Création d'un Questionnaire

| Champ | Valeur |
|-------|--------|
| **Acteur** | Admin |
| **Précondition** | Catégorie ou Produit existe |
| **Postcondition** | Questionnaire créé avec ses questions |

**Scénario nominal :**
1. Admin navigue dans "Questionnaires" → "Créer"
2. Donne un nom (ex: "Questionnaire Auto")
3. Lie le questionnaire à une catégorie ou un produit spécifique
4. Ajoute les questions une par une :
   - Le texte de la question
   - Le type (text, number, select, boolean, date)
   - Les options (pour les selects)
   - L'ordre d'affichage
   - La section (ex: "Profil", "Véhicule")
5. Active le questionnaire
6. Soumet → le questionnaire est prêt à être utilisé

---

## UC-05 : Configuration des Coefficients de Scoring

| Champ | Valeur |
|-------|--------|
| **Acteur** | Admin / Partenaire |
| **Précondition** | Questions existent |
| **Postcondition** | Coefficients définis |

**Scénario nominal (Admin) :**
1. Admin configure les coefficients par défaut pour chaque question
2. Définit un coefficient de base (ex: 1.0 pour neutre, 1.5 pour important, 0.5 pour faible)
3. Optionnellement, peut définir des règles conditionnelles (JSON logic)

**Scénario nominal (Partenaire) :**
1. Partenaire se connecte à son espace
2. Va dans "Scoring"
3. Voit la liste des questions de ses catégories
4. Surcharge le coefficient pour chaque question
5. Sauvegarde → ses nouveaux coefficients sont appliqués pour le scoring de ses produits

---

## UC-06 : Comparaison de Produits (Visiteur)

| Champ | Valeur |
|-------|--------|
| **Acteur** | Visiteur (non connecté) |
| **Précondition** | Produits actifs et questionnaires configurés |
| **Postcondition** | Résultats affichés + lead créé |

**Scénario nominal :**
1. Visiteur arrive sur la page d'accueil
2. Sélectionne une catégorie de produit (ex: "Assurance Auto")
3. Le questionnaire dynamique s'affiche (questions de la catégorie)
4. Remplit ses informations :
   - Questions personnelles (âge, code postal, etc.)
   - Questions spécifiques au produit (type de véhicule, usage, etc.)
5. Soumet le questionnaire
6. Le système :
   a. Calcule un score pour chaque produit de la catégorie
   b. Classe les produits par score (pondéré par les coefficients)
   c. Affiche les résultats avec classement, prix, garanties
7. Le visiteur peut :
   - Voir les détails d'un produit
   - Demander un devis
   - Être redirigé vers le site du partenaire

**Génération du Lead :**
- Un lead est créé automatiquement avec toutes les réponses
- Le lead est attribué au produit et à l'entreprise correspondants
- L'entreprise est notifiée (email, notification in-app)

---

## UC-07 : Modification des Infos Produit par le Partenaire

| Champ | Valeur |
|-------|--------|
| **Acteur** | Partenaire |
| **Précondition** | Partenaire connecté, produit lui appartenant |
| **Postcondition** | Produit mis à jour |

**Scénario nominal :**
1. Partenaire se connecte à son espace
2. Va dans "Mes Produits"
3. Voit la liste de ses produits
4. Clique sur "Modifier" pour un produit
5. Modifie : description, fourchette de prix, garanties (product_details), highlights
6. Peut activer/désactiver la mise en avant
7. Sauvegarde → les modifications sont visibles immédiatement sur le site

---

## UC-08 : Consultation des Leads (Partenaire)

| Champ | Valeur |
|-------|--------|
| **Acteur** | Partenaire |
| **Précondition** | Partenaire connecté |
| **Postcondition** | Lead consulté ou mis à jour |

**Scénario nominal :**
1. Partenaire se connecte à son espace
2. Va dans "Leads"
3. Voit la liste des leads générés pour ses produits
4. Filtre par : date, produit, statut
5. Clique sur un lead pour voir les détails :
   - Informations du prospect
   - Réponses au questionnaire
   - Score calculé
6. Peut changer le statut : "Contacté" → "Qualifié" → "Converti"

---

## UC-09 : Dashboard Partenaire

| Champ | Valeur |
|-------|--------|
| **Acteur** | Partenaire |
| **Précondition** | Partenaire connecté |
| **Postcondition** | Statistiques affichées |

**Scénario nominal :**
1. Partenaire se connecte et arrive sur son dashboard
2. Voit les KPIs :
   - Nombre de leads (aujourd'hui, cette semaine, ce mois)
   - Taux de conversion
   - Produits les plus consultés
   - Évolution dans le temps (graphique)
3. Accès rapide aux dernières actions (nouveaux leads, modifications récentes)
4. Notifications si non lues
