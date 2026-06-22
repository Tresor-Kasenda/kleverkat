<?php

namespace Database\Seeders;

use App\Enums\QuestionInputType;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    /**
     * Questions de base par catégorie (slug).
     * Toutes les 8 valeurs de QuestionInputType sont représentées dans chaque bloc.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $baseQuestions = [
        'assurance' => [
            // step: profil
            ['step_key' => 'profil',    'field_key' => 'date_naissance',    'label' => 'Date de naissance',             'input_type' => 'date',     'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Format JJ/MM/AAAA'],
            ['step_key' => 'profil',    'field_key' => 'genre',             'label' => 'Genre',                         'input_type' => 'radio',    'is_required' => true,  'options_json' => ['masculin' => 'Homme', 'feminin' => 'Femme', 'autre' => 'Autre / Non précisé'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'situation_familiale', 'label' => 'Situation familiale',         'input_type' => 'select',   'is_required' => true,  'options_json' => ['celibataire' => 'Célibataire', 'marie' => 'Marié(e)', 'pacse' => 'Pacsé(e)', 'divorce' => 'Divorcé(e)', 'veuf' => 'Veuf / Veuve'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'code_postal',       'label' => 'Code postal de résidence',      'input_type' => 'text',     'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 75001',      'helper_text' => 'Votre département conditionne l\'offre.'],
            ['step_key' => 'profil',    'field_key' => 'nb_personnes',      'label' => 'Nombre de personnes au foyer',  'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 3',          'helper_text' => 'Adultes + enfants'],
            // step: couverture
            ['step_key' => 'couverture', 'field_key' => 'fumeur',           'label' => 'Êtes-vous fumeur ?',            'input_type' => 'boolean',  'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'La consommation de tabac influence le tarif.'],
            ['step_key' => 'couverture', 'field_key' => 'budget_mensuel',   'label' => 'Budget mensuel maximum (€)',    'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 50',         'helper_text' => null],
            ['step_key' => 'couverture', 'field_key' => 'niveau_couverture', 'label' => 'Niveau de couverture souhaité', 'input_type' => 'select',  'is_required' => true,  'options_json' => ['essentiel' => 'Essentiel', 'confort' => 'Confort', 'premium' => 'Premium'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'couverture', 'field_key' => 'garanties',        'label' => 'Garanties souhaitées',          'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['base' => 'Couverture de base', 'vol' => 'Vol & vandalisme', 'assistance' => 'Assistance 24/7', 'juridique' => 'Protection juridique', 'remplacement' => 'Véhicule de remplacement'], 'placeholder' => null, 'helper_text' => 'Sélectionnez toutes les options souhaitées.'],
            ['step_key' => 'couverture', 'field_key' => 'antecedents',      'label' => 'Avez-vous des antécédents de sinistres ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Sur les 5 dernières années.'],
            // step: informations
            ['step_key' => 'informations', 'field_key' => 'statut_pro',     'label' => 'Statut professionnel',          'input_type' => 'select',   'is_required' => true,  'options_json' => ['salarie' => 'Salarié', 'independant' => 'Indépendant / TNS', 'fonctionnaire' => 'Fonctionnaire', 'retraite' => 'Retraité', 'etudiant' => 'Étudiant', 'sans_emploi' => 'Sans emploi'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'informations', 'field_key' => 'date_debut',     'label' => 'Date de prise d\'effet souhaitée', 'input_type' => 'date', 'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Au plus tôt dans 24 h.'],
            ['step_key' => 'informations', 'field_key' => 'commentaires',   'label' => 'Informations complémentaires',  'input_type' => 'textarea', 'is_required' => false, 'options_json' => null,    'placeholder' => 'Précisez si nécessaire...', 'helper_text' => null],
        ],

        'credit-banque' => [
            // step: profil
            ['step_key' => 'profil',    'field_key' => 'date_naissance',    'label' => 'Date de naissance',             'input_type' => 'date',     'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Vous devez être majeur pour souscrire.'],
            ['step_key' => 'profil',    'field_key' => 'statut_pro',        'label' => 'Situation professionnelle',     'input_type' => 'select',   'is_required' => true,  'options_json' => ['cdi' => 'Salarié CDI', 'cdd' => 'Salarié CDD', 'independant' => 'Indépendant / TNS', 'fonctionnaire' => 'Fonctionnaire', 'retraite' => 'Retraité', 'sans_emploi' => 'Sans emploi'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'revenus_nets',      'label' => 'Revenus mensuels nets (€)',     'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 2500',       'helper_text' => 'Tous revenus confondus du foyer.'],
            ['step_key' => 'profil',    'field_key' => 'ville',             'label' => 'Ville de résidence',            'input_type' => 'text',     'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : Lyon',       'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'proprietaire',      'label' => 'Êtes-vous propriétaire ?',      'input_type' => 'boolean',  'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Propriétaire ou locataire de votre résidence.'],
            // step: projet
            ['step_key' => 'projet',    'field_key' => 'montant',           'label' => 'Montant souhaité (€)',          'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 15000',      'helper_text' => null],
            ['step_key' => 'projet',    'field_key' => 'duree_mois',        'label' => 'Durée de remboursement',        'input_type' => 'select',   'is_required' => true,  'options_json' => ['12' => '12 mois', '24' => '24 mois', '36' => '36 mois', '48' => '48 mois', '60' => '60 mois', '84' => '84 mois', '120' => '10 ans', '180' => '15 ans', '240' => '20 ans', '300' => '25 ans'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'projet',    'field_key' => 'type_garantie',     'label' => 'Type de garantie',             'input_type' => 'radio',    'is_required' => true,  'options_json' => ['aucune' => 'Aucune garantie', 'hypotheque' => 'Hypothèque', 'caution' => 'Caution bancaire', 'nantissement' => 'Nantissement'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'projet',    'field_key' => 'apport',            'label' => 'Avez-vous un apport ?',        'input_type' => 'boolean',  'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Un apport améliore vos conditions de financement.'],
            ['step_key' => 'projet',    'field_key' => 'garanties_souhaitees', 'label' => 'Options souhaitées',        'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['assurance_deces' => 'Assurance décès', 'assurance_invalidite' => 'Assurance invalidité', 'assurance_perte_emploi' => 'Assurance perte d\'emploi', 'modularite' => 'Modularité des mensualités'], 'placeholder' => null, 'helper_text' => null],
            // step: informations
            ['step_key' => 'informations', 'field_key' => 'date_debut',     'label' => 'Date de départ souhaitée',     'input_type' => 'date',     'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => null],
            ['step_key' => 'informations', 'field_key' => 'code_postal',    'label' => 'Code postal',                  'input_type' => 'text',     'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 69001',      'helper_text' => null],
            ['step_key' => 'informations', 'field_key' => 'details_projet', 'label' => 'Détails du projet',            'input_type' => 'textarea', 'is_required' => false, 'options_json' => null,    'placeholder' => 'Décrivez votre projet...', 'helper_text' => null],
        ],

        'energie' => [
            // step: logement
            ['step_key' => 'logement',  'field_key' => 'type_logement',     'label' => 'Type de logement',             'input_type' => 'radio',    'is_required' => true,  'options_json' => ['maison' => 'Maison individuelle', 'appartement' => 'Appartement', 'local_pro' => 'Local professionnel'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'logement',  'field_key' => 'surface_m2',        'label' => 'Surface habitable (m²)',       'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 85',         'helper_text' => null],
            ['step_key' => 'logement',  'field_key' => 'code_postal',       'label' => 'Code postal',                  'input_type' => 'text',     'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 33000',      'helper_text' => 'Détermine votre réseau de distribution.'],
            ['step_key' => 'logement',  'field_key' => 'proprietaire',      'label' => 'Êtes-vous propriétaire ?',     'input_type' => 'boolean',  'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => null],
            ['step_key' => 'logement',  'field_key' => 'nb_occupants',      'label' => 'Nombre d\'occupants',          'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 4',          'helper_text' => null],
            // step: consommation
            ['step_key' => 'consommation', 'field_key' => 'conso_annuelle', 'label' => 'Consommation annuelle estimée', 'input_type' => 'select',  'is_required' => true,  'options_json' => ['moins_3000' => '< 3 000 kWh', '3000_6000' => '3 000–6 000 kWh', '6000_12000' => '6 000–12 000 kWh', 'plus_12000' => '> 12 000 kWh'], 'placeholder' => 'Choisissez', 'helper_text' => 'Visible sur votre facture actuelle.'],
            ['step_key' => 'consommation', 'field_key' => 'compteur_connecte', 'label' => 'Votre compteur est-il connecté (Linky) ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'consommation', 'field_key' => 'systemes_chauffage', 'label' => 'Systèmes de chauffage utilisés', 'input_type' => 'checkbox', 'is_required' => true, 'options_json' => ['radiateurs_elec' => 'Radiateurs électriques', 'chaudiere_gaz' => 'Chaudière gaz', 'pac' => 'Pompe à chaleur', 'poele' => 'Poêle à bois / pellets', 'fioul' => 'Fioul'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'consommation', 'field_key' => 'option_tarifaire', 'label' => 'Option tarifaire actuelle',  'input_type' => 'radio',    'is_required' => true,  'options_json' => ['base' => 'Option Base', 'heures_creuses' => 'Heures Creuses / Pleines', 'tempo' => 'Tempo', 'ej_zen' => 'Effacement Jour de Pointe'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'consommation', 'field_key' => 'date_changement', 'label' => 'Date de changement souhaitée', 'input_type' => 'date',   'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => 'Délai minimum : 14 jours.'],
            // step: contrat
            ['step_key' => 'contrat',   'field_key' => 'engagement',        'label' => 'Durée d\'engagement',          'input_type' => 'select',   'is_required' => true,  'options_json' => ['sans' => 'Sans engagement', '12' => '12 mois', '24' => '24 mois'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'contrat',   'field_key' => 'fournisseur_actuel', 'label' => 'Fournisseur actuel',          'input_type' => 'text',     'is_required' => false, 'options_json' => null,    'placeholder' => 'Ex : EDF, Engie…', 'helper_text' => null],
            ['step_key' => 'contrat',   'field_key' => 'commentaires',      'label' => 'Besoins particuliers',         'input_type' => 'textarea', 'is_required' => false, 'options_json' => null,    'placeholder' => 'Précisez vos attentes...', 'helper_text' => null],
        ],

        'telecom' => [
            // step: profil
            ['step_key' => 'profil',    'field_key' => 'type_utilisation',  'label' => 'Type d\'utilisation',          'input_type' => 'radio',    'is_required' => true,  'options_json' => ['personnel' => 'Personnel', 'professionnel' => 'Professionnel', 'famille' => 'Famille / Multi-utilisateurs'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'nb_utilisateurs',   'label' => 'Nombre d\'utilisateurs',       'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 1',          'helper_text' => null],
            ['step_key' => 'profil',    'field_key' => 'code_postal',       'label' => 'Code postal',                  'input_type' => 'text',     'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 13001',      'helper_text' => 'Pour vérifier la couverture réseau.'],
            ['step_key' => 'profil',    'field_key' => 'eligible_fibre',    'label' => 'Êtes-vous éligible à la fibre ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Vérifiable sur le site de votre opérateur.'],
            ['step_key' => 'profil',    'field_key' => 'date_activation',   'label' => 'Date d\'activation souhaitée', 'input_type' => 'date',    'is_required' => true,  'options_json' => null,    'placeholder' => null,              'helper_text' => null],
            // step: besoins
            ['step_key' => 'besoins',   'field_key' => 'volume_data',       'label' => 'Volume de données mensuel',    'input_type' => 'select',   'is_required' => true,  'options_json' => ['5go' => '5 Go', '20go' => '20 Go', '50go' => '50 Go', '100go' => '100 Go', '200go' => '200 Go', 'illimite' => 'Illimité'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'besoins',   'field_key' => 'options',           'label' => 'Options souhaitées',           'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['appels_intl' => 'Appels internationaux', 'roaming' => 'Roaming inclus', 'tv' => 'TV incluse', 'telephone' => 'Téléphone inclus', 'cloud' => 'Stockage cloud'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'besoins',   'field_key' => 'engagement',        'label' => 'Engagement souhaité',          'input_type' => 'radio',    'is_required' => true,  'options_json' => ['sans' => 'Sans engagement', '12mois' => '12 mois', '24mois' => '24 mois'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'besoins',   'field_key' => 'budget_mensuel',    'label' => 'Budget mensuel maximum (€)',   'input_type' => 'number',   'is_required' => true,  'options_json' => null,    'placeholder' => 'Ex : 30',         'helper_text' => null],
            // step: configuration
            ['step_key' => 'configuration', 'field_key' => 'resilier',      'label' => 'Avez-vous un abonnement à résilier ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Votre nouvel opérateur peut s\'en charger.'],
            ['step_key' => 'configuration', 'field_key' => 'operateur_actuel', 'label' => 'Opérateur actuel',         'input_type' => 'select',   'is_required' => false, 'options_json' => ['orange' => 'Orange', 'sfr' => 'SFR', 'bouygues' => 'Bouygues Telecom', 'free' => 'Free', 'autres_mvno' => 'Autre / MVNO'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'configuration', 'field_key' => 'besoins_specifiques', 'label' => 'Besoins spécifiques',  'input_type' => 'textarea', 'is_required' => false, 'options_json' => null,    'placeholder' => 'Précisez vos besoins...', 'helper_text' => null],
        ],
    ];

    /**
     * Questions supplémentaires par secteur (slug du secteur).
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $sectorQuestions = [
        'assurance-auto' => [
            ['step_key' => 'vehicule', 'field_key' => 'marque',          'label' => 'Marque du véhicule',        'input_type' => 'text',    'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : Renault', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'annee_mise_circ', 'label' => 'Année de mise en circulation', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 2019', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'usage',           'label' => 'Usage du véhicule',         'input_type' => 'select',  'is_required' => true,  'options_json' => ['prive' => 'Privé uniquement', 'trajet_travail' => 'Trajet domicile-travail', 'pro' => 'Professionnel'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'bonus_malus',     'label' => 'Coefficient bonus-malus',   'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 0.85', 'helper_text' => 'Visible sur votre relevé d\'information.'],
        ],
        'assurance-moto' => [
            ['step_key' => 'vehicule', 'field_key' => 'type_deux_roues', 'label' => 'Type de deux-roues',        'input_type' => 'radio',   'is_required' => true,  'options_json' => ['moto' => 'Moto', 'scooter' => 'Scooter', 'cyclomoteur' => 'Cyclomoteur < 50cc'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'cylindree',       'label' => 'Cylindrée (cc)',            'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 650', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'annee',           'label' => 'Année du véhicule',         'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 2021', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'usage_saisonnier', 'label' => 'Usage saisonnier ?',       'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Moins de 6 mois d\'utilisation par an.'],
        ],
        'assurance-habitation' => [
            ['step_key' => 'bien',     'field_key' => 'type_bien',       'label' => 'Type de bien',              'input_type' => 'radio',   'is_required' => true,  'options_json' => ['maison' => 'Maison', 'appartement' => 'Appartement', 'studio' => 'Studio', 'mobil_home' => 'Mobil-home'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'surface_m2',      'label' => 'Surface (m²)',              'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 75', 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'etage',           'label' => 'Étage (0 = RDC)',           'input_type' => 'number',  'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 2', 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'alarme',          'label' => 'Dispositif d\'alarme installé ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Peut réduire la prime.'],
        ],
        'mutuelle-sante' => [
            ['step_key' => 'besoins_sante', 'field_key' => 'optique_annuel',  'label' => 'Dépenses optique annuelles (€)',  'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 300', 'helper_text' => null],
            ['step_key' => 'besoins_sante', 'field_key' => 'dentaire_annuel', 'label' => 'Dépenses dentaires annuelles (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 200', 'helper_text' => null],
            ['step_key' => 'besoins_sante', 'field_key' => 'medecin_traitant', 'label' => 'Avez-vous un médecin traitant ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Affecte le niveau de remboursement.'],
            ['step_key' => 'besoins_sante', 'field_key' => 'medecine_douce', 'label' => 'Souhaitez-vous des remboursements médecines douces ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Ostéopathie, acupuncture, etc.'],
        ],
        'assurance-emprunteur' => [
            ['step_key' => 'pret',     'field_key' => 'montant_pret',    'label' => 'Montant du crédit (€)',     'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 250000', 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'duree_pret_ans',  'label' => 'Durée du prêt (années)',    'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 20', 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'quotite',         'label' => 'Quotité couverte (%)',      'input_type' => 'select',  'is_required' => true,  'options_json' => ['100' => '100%', '150' => '150% (2 têtes)', '200' => '200% (2 têtes 100/100)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'sport_extreme',   'label' => 'Pratiquez-vous un sport extrême ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Parachute, sports de combat, alpinisme...'],
        ],
        'assurance-animaux' => [
            ['step_key' => 'animal',   'field_key' => 'espece',          'label' => 'Espèce de l\'animal',       'input_type' => 'radio',   'is_required' => true,  'options_json' => ['chien' => 'Chien', 'chat' => 'Chat', 'lapin' => 'Lapin / NAC'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'animal',   'field_key' => 'age_animal',      'label' => 'Âge de l\'animal (années)', 'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 3', 'helper_text' => null],
            ['step_key' => 'animal',   'field_key' => 'race',            'label' => 'Race',                      'input_type' => 'text',    'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : Labrador', 'helper_text' => null],
            ['step_key' => 'animal',   'field_key' => 'vaccine',         'label' => 'À jour des vaccinations ?', 'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'assurance-voyage' => [
            ['step_key' => 'voyage',   'field_key' => 'destination',     'label' => 'Destination principale',    'input_type' => 'select',  'is_required' => true,  'options_json' => ['ue' => 'Union Européenne', 'europe_hors_ue' => 'Europe hors UE', 'amerique_nord' => 'Amérique du Nord', 'amerique_sud' => 'Amérique du Sud', 'asie' => 'Asie', 'afrique' => 'Afrique', 'monde_entier' => 'Monde entier'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'voyage',   'field_key' => 'duree_sejour',    'label' => 'Durée du séjour (jours)',   'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 14', 'helper_text' => null],
            ['step_key' => 'voyage',   'field_key' => 'activites_sport', 'label' => 'Activités sportives prévues ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Ski, plongée, randonnée altitude...'],
            ['step_key' => 'voyage',   'field_key' => 'motif_voyage',    'label' => 'Motif du voyage',           'input_type' => 'radio',   'is_required' => true,  'options_json' => ['tourisme' => 'Tourisme', 'affaires' => 'Affaires', 'etudes' => 'Études', 'expatriation' => 'Expatriation'], 'placeholder' => null, 'helper_text' => null],
        ],
        'assurance-scolaire' => [
            ['step_key' => 'enfant',   'field_key' => 'age_enfant',      'label' => 'Âge de l\'enfant',          'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 8', 'helper_text' => null],
            ['step_key' => 'enfant',   'field_key' => 'niveau_scolaire', 'label' => 'Niveau scolaire',           'input_type' => 'select',  'is_required' => true,  'options_json' => ['maternelle' => 'Maternelle', 'primaire' => 'Primaire (CP–CM2)', 'college' => 'Collège', 'lycee' => 'Lycée'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'enfant',   'field_key' => 'activites_extra', 'label' => 'Activités extrascolaires',  'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['sport' => 'Sport', 'musique' => 'Musique', 'theatre' => 'Théâtre', 'sejour_linguistique' => 'Séjour linguistique'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'enfant',   'field_key' => 'transport_scolaire', 'label' => 'Transport scolaire utilisé ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'assurance-vie' => [
            ['step_key' => 'investissement', 'field_key' => 'horizon_placement', 'label' => 'Horizon de placement', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['court' => 'Court terme (< 5 ans)', 'moyen' => 'Moyen terme (5–10 ans)', 'long' => 'Long terme (> 10 ans)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'investissement', 'field_key' => 'profil_risque', 'label' => 'Profil de risque',       'input_type' => 'radio',   'is_required' => true,  'options_json' => ['prudent' => 'Prudent (capital garanti)', 'equilibre' => 'Équilibré (mix sécurité/perf)', 'dynamique' => 'Dynamique (rendement prioritaire)'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'investissement', 'field_key' => 'versement_mensuel', 'label' => 'Versement mensuel souhaité (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 200', 'helper_text' => null],
            ['step_key' => 'investissement', 'field_key' => 'capital_initial', 'label' => 'Capital initial à investir (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 10000', 'helper_text' => null],
        ],
        'prevoyance-individuelle' => [
            ['step_key' => 'situation', 'field_key' => 'profession',      'label' => 'Profession',                'input_type' => 'text',    'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : Infirmier', 'helper_text' => 'Influe sur les garanties invalidité.'],
            ['step_key' => 'situation', 'field_key' => 'revenus_annuels', 'label' => 'Revenus annuels bruts (€)', 'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 35000', 'helper_text' => null],
            ['step_key' => 'situation', 'field_key' => 'personnes_charge', 'label' => 'Personnes à charge',       'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 2', 'helper_text' => null],
            ['step_key' => 'situation', 'field_key' => 'couverture_existante', 'label' => 'Couverture prévoyance existante ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Contrat employeur ou collectif existant.'],
        ],

        'credit-immobilier' => [
            ['step_key' => 'bien',     'field_key' => 'type_bien',        'label' => 'Type de bien',             'input_type' => 'radio',   'is_required' => true,  'options_json' => ['neuf' => 'Neuf (VEFA)', 'ancien' => 'Ancien', 'terrain' => 'Terrain + construction'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'departement',      'label' => 'Département du bien',      'input_type' => 'text',    'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 75', 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'usage_bien',       'label' => 'Usage du bien',            'input_type' => 'select',  'is_required' => true,  'options_json' => ['residence_principale' => 'Résidence principale', 'residence_secondaire' => 'Résidence secondaire', 'investissement_locatif' => 'Investissement locatif'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'bien',     'field_key' => 'primo_accedant',   'label' => 'Êtes-vous primo-accédant ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Aucun bien immobilier depuis 2 ans.'],
        ],
        'credit-a-la-consommation' => [
            ['step_key' => 'projet',   'field_key' => 'objet_pret',       'label' => 'Objet du prêt',            'input_type' => 'select',  'is_required' => true,  'options_json' => ['auto' => 'Achat véhicule', 'travaux' => 'Travaux', 'voyage' => 'Vacances', 'sante' => 'Santé', 'equipement' => 'Équipement maison', 'autre' => 'Autre projet'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'projet',   'field_key' => 'charges_mensuelles', 'label' => 'Charges mensuelles actuelles (€)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 600', 'helper_text' => 'Loyer + crédits en cours.'],
            ['step_key' => 'projet',   'field_key' => 'incidents_bancaires', 'label' => 'Avez-vous eu des incidents bancaires ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Interdit bancaire, découvert non autorisé...'],
            ['step_key' => 'projet',   'field_key' => 'remboursement_mensuel_max', 'label' => 'Mensualité max acceptable (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 300', 'helper_text' => null],
        ],
        'rachat-de-credits' => [
            ['step_key' => 'credits',  'field_key' => 'nb_credits',       'label' => 'Nombre de crédits à regrouper', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 4', 'helper_text' => null],
            ['step_key' => 'credits',  'field_key' => 'encours_total',    'label' => 'Encours total (€)',         'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 45000', 'helper_text' => 'Somme de tous vos capitaux restants dûs.'],
            ['step_key' => 'credits',  'field_key' => 'mensualites_actuelles', 'label' => 'Mensualités totales actuelles (€)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 1200', 'helper_text' => null],
            ['step_key' => 'credits',  'field_key' => 'objectif_rachat',  'label' => 'Objectif principal',        'input_type' => 'radio',   'is_required' => true,  'options_json' => ['reduire_mensualite' => 'Réduire la mensualité', 'simplifier' => 'Simplifier les remboursements', 'tresorerie' => 'Débloquer de la trésorerie'], 'placeholder' => null, 'helper_text' => null],
        ],
        'epargne-placements' => [
            ['step_key' => 'projet',   'field_key' => 'objectif_epargne', 'label' => 'Objectif principal',       'input_type' => 'select',  'is_required' => true,  'options_json' => ['precaution' => 'Épargne de précaution', 'retraite' => 'Préparer la retraite', 'immobilier' => 'Projet immobilier', 'transmission' => 'Transmission / Succession'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'projet',   'field_key' => 'epargne_mensuelle', 'label' => 'Capacité d\'épargne mensuelle (€)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 300', 'helper_text' => null],
            ['step_key' => 'projet',   'field_key' => 'capital_disponible', 'label' => 'Capital déjà disponible (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 5000', 'helper_text' => null],
            ['step_key' => 'projet',   'field_key' => 'imposition_tranche', 'label' => 'Tranche marginale d\'imposition', 'input_type' => 'radio', 'is_required' => false, 'options_json' => ['0' => '0%', '11' => '11%', '30' => '30%', '41' => '41%', '45' => '45%'], 'placeholder' => null, 'helper_text' => 'Influe sur la fiscalité des placements.'],
        ],
        'compte-bancaire' => [
            ['step_key' => 'besoins',  'field_key' => 'operations_mois',  'label' => 'Nombre d\'opérations par mois', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['moins_10' => '< 10', '10_30' => '10–30', 'plus_30' => '> 30'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'besoins',  'field_key' => 'virements_internationaux', 'label' => 'Virements internationaux réguliers ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'besoins',  'field_key' => 'agence_physique',  'label' => 'Besoin d\'une agence physique ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'besoins',  'field_key' => 'services_inclus',  'label' => 'Services prioritaires',     'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['assurance_carte' => 'Assurances carte', 'decouvert_autorise' => 'Découvert autorisé', 'epargne_integree' => 'Livret intégré', 'conseiller_dedie' => 'Conseiller dédié'], 'placeholder' => null, 'helper_text' => null],
        ],
        'carte-bancaire' => [
            ['step_key' => 'usage',    'field_key' => 'paiements_etrangers', 'label' => 'Paiements fréquents à l\'étranger ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'usage',    'field_key' => 'volume_achats_mois', 'label' => 'Volume d\'achats mensuel (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 800', 'helper_text' => null],
            ['step_key' => 'usage',    'field_key' => 'type_carte',        'label' => 'Type de carte souhaité',   'input_type' => 'radio',   'is_required' => true,  'options_json' => ['debit' => 'Débit immédiat', 'debit_differe' => 'Débit différé', 'credit' => 'Crédit', 'prepayee' => 'Prépayée'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'usage',    'field_key' => 'avantages',         'label' => 'Avantages recherchés',     'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['cashback' => 'Cashback', 'miles' => 'Miles compagnies', 'assurances' => 'Assurances voyages', 'lounge' => 'Accès salons aéroports'], 'placeholder' => null, 'helper_text' => null],
        ],
        'credit-auto' => [
            ['step_key' => 'vehicule', 'field_key' => 'type_financement', 'label' => 'Type de financement',      'input_type' => 'radio',   'is_required' => true,  'options_json' => ['pret_classique' => 'Prêt classique', 'loa' => 'LOA (Location avec option)', 'lld' => 'LLD (sans option)'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'vehicule_neuf',    'label' => 'Véhicule neuf ?',          'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'prix_vehicule',    'label' => 'Prix du véhicule (€)',     'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 25000', 'helper_text' => null],
            ['step_key' => 'vehicule', 'field_key' => 'energie_vehicule', 'label' => 'Type d\'énergie',         'input_type' => 'select',  'is_required' => true,  'options_json' => ['essence' => 'Essence', 'diesel' => 'Diesel', 'hybride' => 'Hybride', 'electrique' => 'Électrique', 'gpl' => 'GPL'], 'placeholder' => 'Choisissez', 'helper_text' => null],
        ],
        'pret-etudiant' => [
            ['step_key' => 'etudes',   'field_key' => 'etablissement',    'label' => 'Type d\'établissement',   'input_type' => 'select',  'is_required' => true,  'options_json' => ['universite' => 'Université', 'grande_ecole' => 'Grande École', 'bts_iut' => 'BTS / IUT', 'ecole_prive' => 'École privée'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'etudes',   'field_key' => 'annee_etudes',     'label' => 'Niveau d\'études (bac+)', 'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 3', 'helper_text' => null],
            ['step_key' => 'etudes',   'field_key' => 'garant',           'label' => 'Avez-vous un garant ?',  'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Parent ou garant bancaire.'],
            ['step_key' => 'etudes',   'field_key' => 'domaine_etudes',   'label' => 'Domaine d\'études',       'input_type' => 'text',    'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : Droit, Médecine', 'helper_text' => null],
        ],
        'bourse-investissement' => [
            ['step_key' => 'investisseur', 'field_key' => 'experience_bourse', 'label' => 'Expérience en bourse', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['debutant' => 'Débutant (< 1 an)', 'intermediaire' => 'Intermédiaire (1–5 ans)', 'experimente' => 'Expérimenté (> 5 ans)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'investisseur', 'field_key' => 'frequence_operations', 'label' => 'Fréquence des opérations', 'input_type' => 'radio', 'is_required' => true, 'options_json' => ['rare' => 'Rare (< 1/mois)', 'regulier' => 'Régulier (1–10/mois)', 'actif' => 'Actif (> 10/mois)'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'investisseur', 'field_key' => 'marches_vises', 'label' => 'Marchés visés',           'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['actions_fr' => 'Actions françaises', 'actions_eu' => 'Actions européennes', 'etf' => 'ETF / Trackers', 'crypto' => 'Cryptomonnaies', 'matieres_premieres' => 'Matières premières'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'investisseur', 'field_key' => 'capital_investi', 'label' => 'Capital à investir (€)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 5000', 'helper_text' => null],
        ],
        'assurance-emprunteur-banque' => [
            ['step_key' => 'pret',     'field_key' => 'banque_preteur',   'label' => 'Banque prêteuse',          'input_type' => 'select',  'is_required' => true,  'options_json' => ['bnp' => 'BNP Paribas', 'credit_agricole' => 'Crédit Agricole', 'societe_generale' => 'Société Générale', 'lcl' => 'LCL', 'caisse_epargne' => 'Caisse d\'Épargne', 'banque_populaire' => 'Banque Populaire', 'autre' => 'Autre'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'type_pret',        'label' => 'Type de prêt',            'input_type' => 'radio',   'is_required' => true,  'options_json' => ['immobilier' => 'Immobilier', 'pro' => 'Professionnel', 'consommation' => 'Consommation'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'montant_credit',   'label' => 'Montant du crédit (€)',    'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 180000', 'helper_text' => null],
            ['step_key' => 'pret',     'field_key' => 'profession_risque', 'label' => 'Profession à risque ?',  'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Pompier, militaire, plongeur pro...'],
        ],

        'electricite' => [
            ['step_key' => 'compteur', 'field_key' => 'puissance_kva',    'label' => 'Puissance souscrite (kVA)', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['3' => '3 kVA', '6' => '6 kVA', '9' => '9 kVA', '12' => '12 kVA', '15' => '15 kVA', '18' => '18 kVA', '24' => '24 kVA', '36' => '36 kVA'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'facture_annuelle', 'label' => 'Montant facture annuelle estimée (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 1200', 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'type_contrat',     'label' => 'Type de contrat souhaité', 'input_type' => 'radio',  'is_required' => true,  'options_json' => ['fixe' => 'Prix fixe', 'indexe' => 'Indexé TRVe', 'vert' => 'Énergie verte'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'pdt_installation', 'label' => 'Installation spéciale (piscine, sauna, borne VE...)', 'input_type' => 'boolean', 'is_required' => false, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'gaz-naturel' => [
            ['step_key' => 'compteur', 'field_key' => 'usage_gaz',        'label' => 'Usage du gaz',             'input_type' => 'checkbox', 'is_required' => true, 'options_json' => ['chauffage' => 'Chauffage', 'eau_chaude' => 'Eau chaude sanitaire', 'cuisson' => 'Cuisson'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'conso_mwh',        'label' => 'Consommation annuelle estimée (MWh)', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['moins_6' => '< 6 MWh', '6_12' => '6–12 MWh', '12_20' => '12–20 MWh', 'plus_20' => '> 20 MWh'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'type_contrat_gaz', 'label' => 'Type de contrat souhaité', 'input_type' => 'radio',  'is_required' => true,  'options_json' => ['fixe' => 'Prix fixe', 'indexe' => 'Indexé TRVg', 'vert' => 'Gaz vert'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'compteur', 'field_key' => 'reference_pce',    'label' => 'Référence PCE (si connue)', 'input_type' => 'text',   'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 0123456789012', 'helper_text' => 'Figurant sur votre facture actuelle.'],
        ],
        'electricite-gaz' => [
            ['step_key' => 'double',   'field_key' => 'raison_combo',     'label' => 'Raison du passage au duo', 'input_type' => 'select',  'is_required' => true,  'options_json' => ['simplification' => 'Simplifier mes contrats', 'economie' => 'Faire des économies', 'vert' => 'Passer à l\'énergie verte', 'fin_tarif_reglemente' => 'Fin du tarif réglementé'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'double',   'field_key' => 'conso_elec_annuelle', 'label' => 'Consommation électricité annuelle (kWh)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 4500', 'helper_text' => null],
            ['step_key' => 'double',   'field_key' => 'conso_gaz_annuelle', 'label' => 'Consommation gaz annuelle (MWh)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 12', 'helper_text' => null],
            ['step_key' => 'double',   'field_key' => 'facturation_unique', 'label' => 'Souhaitez-vous une facture unique ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'energie-verte' => [
            ['step_key' => 'engagement', 'field_key' => 'certificats_origine', 'label' => 'Importance des garanties d\'origine', 'input_type' => 'radio', 'is_required' => true, 'options_json' => ['indispensable' => 'Indispensable', 'important' => 'Important', 'neutre' => 'Neutre'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'engagement', 'field_key' => 'type_renouvelable', 'label' => 'Source préférée',       'input_type' => 'select',  'is_required' => false, 'options_json' => ['solaire' => 'Solaire', 'eolien' => 'Éolien', 'hydraulique' => 'Hydraulique', 'mix' => 'Mix renouvelable'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'engagement', 'field_key' => 'local_production', 'label' => 'Production locale importante ?', 'input_type' => 'boolean', 'is_required' => false, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Préférence pour un producteur de votre région.'],
            ['step_key' => 'engagement', 'field_key' => 'budget_prime_verte', 'label' => 'Budget prime énergie verte (€/an)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 50', 'helper_text' => 'Supplément accepté pour l\'énergie verte.'],
        ],
        'panneaux-solaires' => [
            ['step_key' => 'installation', 'field_key' => 'type_toiture', 'label' => 'Type de toiture',          'input_type' => 'radio',   'is_required' => true,  'options_json' => ['tuiles' => 'Tuiles', 'ardoise' => 'Ardoise', 'bac_acier' => 'Bac acier', 'terrasse' => 'Toiture terrasse'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'orientation', 'label' => 'Orientation principale',    'input_type' => 'select',  'is_required' => true,  'options_json' => ['sud' => 'Sud', 'sud_est' => 'Sud-Est', 'sud_ouest' => 'Sud-Ouest', 'est_ouest' => 'Est / Ouest'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'puissance_voulue', 'label' => 'Puissance souhaitée (kWc)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 6', 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'revente_surplus', 'label' => 'Souhaitez-vous revendre le surplus ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Obligation d\'achat EDF OA.'],
        ],
        'pompe-a-chaleur' => [
            ['step_key' => 'installation', 'field_key' => 'systeme_distribution', 'label' => 'Système de distribution existant', 'input_type' => 'radio', 'is_required' => true, 'options_json' => ['radiateurs' => 'Radiateurs', 'plancher_chauffant' => 'Plancher chauffant', 'ventilo' => 'Ventilo-convecteurs', 'aucun' => 'Aucun (neuf)'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'surface_a_chauffer', 'label' => 'Surface à chauffer (m²)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 120', 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'zone_climatique', 'label' => 'Zone climatique',         'input_type' => 'select',  'is_required' => true,  'options_json' => ['h1' => 'H1 (Nord)', 'h2' => 'H2 (Centre)', 'h3' => 'H3 (Sud / littoral)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'aide_maprimrenov', 'label' => 'Êtes-vous éligible à MaPrimeRénov\' ?', 'input_type' => 'boolean', 'is_required' => false, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Conditionné aux ressources du foyer.'],
        ],
        'chaudiere' => [
            ['step_key' => 'remplacement', 'field_key' => 'chaudiere_actuelle', 'label' => 'Type de chaudière actuelle', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['gaz_ancienne' => 'Gaz ancienne (< condensation)', 'gaz_condensation' => 'Gaz à condensation', 'fioul' => 'Fioul', 'electrique' => 'Électrique', 'aucune' => 'Aucune (installation neuve)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'remplacement', 'field_key' => 'age_chaudiere',  'label' => 'Âge de la chaudière actuelle (ans)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 15', 'helper_text' => null],
            ['step_key' => 'remplacement', 'field_key' => 'urgence',        'label' => 'Remplacement urgent ?',     'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Panne ou danger signalé.'],
            ['step_key' => 'remplacement', 'field_key' => 'besoin_eau_chaude', 'label' => 'Chauffe-eau sanitaire intégré souhaité ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'isolation-thermique' => [
            ['step_key' => 'travaux',  'field_key' => 'zones_isolation',   'label' => 'Zones à isoler',           'input_type' => 'checkbox', 'is_required' => true, 'options_json' => ['combles' => 'Combles', 'murs_int' => 'Murs intérieurs', 'murs_ext' => 'Murs extérieurs (ITE)', 'plancher' => 'Plancher bas', 'fenetres' => 'Fenêtres / Portes'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'travaux',  'field_key' => 'annee_construction', 'label' => 'Année de construction',   'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 1975', 'helper_text' => null],
            ['step_key' => 'travaux',  'field_key' => 'etiquette_dpe',     'label' => 'Étiquette DPE actuelle',   'input_type' => 'radio',   'is_required' => false, 'options_json' => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F', 'g' => 'G'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'travaux',  'field_key' => 'budget_travaux',    'label' => 'Budget travaux envisagé (€)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 15000', 'helper_text' => null],
        ],
        'ballon-thermodynamique' => [
            ['step_key' => 'installation', 'field_key' => 'volume_litres', 'label' => 'Volume souhaité (litres)', 'input_type' => 'select',  'is_required' => true,  'options_json' => ['100' => '100 L', '150' => '150 L', '200' => '200 L', '250' => '250 L', '300' => '300 L'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'espace_dispo',  'label' => 'Espace disponible (m²)',  'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 4', 'helper_text' => 'Surface de la pièce d\'installation.'],
            ['step_key' => 'installation', 'field_key' => 'type_prise_air', 'label' => 'Prise d\'air',           'input_type' => 'radio',   'is_required' => true,  'options_json' => ['ambiant' => 'Air ambiant (intérieur)', 'exterieur' => 'Air extérieur (gainable)', 'split' => 'Split (unité extérieure)'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'installation', 'field_key' => 'remplacement_chauffe_eau', 'label' => 'Remplacement d\'un chauffe-eau existant ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
        ],
        'audit-energetique' => [
            ['step_key' => 'mission',  'field_key' => 'type_audit',        'label' => 'Type d\'audit recherché', 'input_type' => 'select',  'is_required' => true,  'options_json' => ['dpe' => 'DPE simple', 'audit_reglementaire' => 'Audit réglementaire', 'audit_approfondi' => 'Audit approfondi', 'copropriete' => 'Audit copropriété'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'mission',  'field_key' => 'urgence_audit',     'label' => 'Besoin urgent (< 15 jours) ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Vente, dossier travaux, obligation légale.'],
            ['step_key' => 'mission',  'field_key' => 'annee_construction_audit', 'label' => 'Année de construction', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 1968', 'helper_text' => null],
            ['step_key' => 'mission',  'field_key' => 'objectif_audit',    'label' => 'Objectif principal',       'input_type' => 'radio',   'is_required' => true,  'options_json' => ['vente_location' => 'Vente / Location obligatoire', 'travaux' => 'Préparer des travaux', 'aides' => 'Obtenir des aides', 'information' => 'Information générale'], 'placeholder' => null, 'helper_text' => null],
        ],

        'forfait-mobile' => [
            ['step_key' => 'mobile',   'field_key' => 'reseau_prefere',    'label' => 'Réseau préféré',           'input_type' => 'radio',   'is_required' => false, 'options_json' => ['orange' => 'Orange', 'sfr' => 'SFR', 'bouygues' => 'Bouygues', 'free' => 'Free', 'indifferent' => 'Indifférent'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'mobile',   'field_key' => 'appels_internationaux', 'label' => 'Appels internationaux fréquents ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'mobile',   'field_key' => 'telephone_inclus',  'label' => 'Téléphone inclus ?',       'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Paiement étalé inclus dans le forfait.'],
            ['step_key' => 'mobile',   'field_key' => 'generation_reseau', 'label' => 'Génération réseau souhaitée', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['4g' => '4G', '5g' => '5G', 'indifferent' => 'Indifférent'], 'placeholder' => 'Choisissez', 'helper_text' => null],
        ],
        'box-internet' => [
            ['step_key' => 'connexion', 'field_key' => 'type_connexion',   'label' => 'Type de connexion disponible', 'input_type' => 'radio', 'is_required' => true, 'options_json' => ['fibre_ftth' => 'Fibre FTTH', 'adsl' => 'ADSL', 'cable' => 'Câble', 'non_sais' => 'Je ne sais pas'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'connexion', 'field_key' => 'debit_minimum',    'label' => 'Débit minimum souhaité',   'input_type' => 'select',  'is_required' => true,  'options_json' => ['30' => '30 Mb (basique)', '100' => '100 Mb (streaming)', '500' => '500 Mb (famille)', '1000' => '1 Gb (gaming / télétravail)'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'connexion', 'field_key' => 'tv_incluse',       'label' => 'TV incluse souhaitée ?',   'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'connexion', 'field_key' => 'nb_appareils',     'label' => 'Nombre d\'appareils connectés', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 8', 'helper_text' => null],
        ],
        'fibre-optique' => [
            ['step_key' => 'fibre',    'field_key' => 'usage_principal',   'label' => 'Usage principal',          'input_type' => 'checkbox', 'is_required' => true, 'options_json' => ['teletravail' => 'Télétravail', 'gaming' => 'Gaming', 'streaming_4k' => 'Streaming 4K', 'multiposte' => 'Multi-postes famille'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'fibre',    'field_key' => 'debit_montant',     'label' => 'Débit montant important ?', 'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Pour upload vidéo, visioconférence, backup cloud.'],
            ['step_key' => 'fibre',    'field_key' => 'prise_ftth',        'label' => 'Prise fibre installée dans le logement ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'fibre',    'field_key' => 'forfait_pro',       'label' => 'Usage professionnel ?',     'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'SLA et assistance prioritaire disponibles.'],
        ],
        'adsl' => [
            ['step_key' => 'adsl',     'field_key' => 'distance_nra',      'label' => 'Distance approximative au central téléphonique', 'input_type' => 'select', 'is_required' => false, 'options_json' => ['moins_500m' => '< 500 m', '500_1000m' => '500 m – 1 km', '1_3km' => '1–3 km', 'plus_3km' => '> 3 km', 'inconnue' => 'Je ne sais pas'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'adsl',     'field_key' => 'fibre_prevue',      'label' => 'Fibre prévue dans votre zone ?', 'input_type' => 'boolean', 'is_required' => false, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Vérifiable sur arcep.fr.'],
            ['step_key' => 'adsl',     'field_key' => 'telephone_fixe_utilise', 'label' => 'Téléphone fixe utilisé ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'adsl',     'field_key' => 'usage_adsl',        'label' => 'Usage principal',          'input_type' => 'select',  'is_required' => true,  'options_json' => ['navigation' => 'Navigation web', 'email' => 'E-mail / bureautique', 'streaming_sd' => 'Streaming SD', 'voip' => 'VoIP / Visioconférence'], 'placeholder' => 'Choisissez', 'helper_text' => null],
        ],
        'triple-play' => [
            ['step_key' => 'triple',   'field_key' => 'chaines_minimales', 'label' => 'Nombre minimal de chaînes TV', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['basique' => '< 50 chaînes', 'standard' => '50–100 chaînes', 'elargi' => '100–200 chaînes', 'premium' => '> 200 chaînes'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'triple',   'field_key' => 'bouquets_sport',    'label' => 'Bouquets sport importants ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Canal+, beIN Sports, RMC Sport.'],
            ['step_key' => 'triple',   'field_key' => 'nb_decodeurs',      'label' => 'Nombre de décodeurs TV',   'input_type' => 'number',  'is_required' => true,  'options_json' => null, 'placeholder' => 'Ex : 2', 'helper_text' => null],
            ['step_key' => 'triple',   'field_key' => 'svod_inclus',       'label' => 'SVoD inclus souhaité',     'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['netflix' => 'Netflix', 'disney' => 'Disney+', 'amazon' => 'Amazon Prime', 'canal_plus' => 'Canal+'], 'placeholder' => null, 'helper_text' => null],
        ],
        'internet-mobile-5g' => [
            ['step_key' => '5g',       'field_key' => 'couverture_5g',     'label' => 'Couverture 5G dans votre zone ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Vérifiable sur le site de l\'ARCEP.'],
            ['step_key' => '5g',       'field_key' => 'usage_mobilite',    'label' => 'Usage en déplacement principalement ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => '5g',       'field_key' => 'type_equipement',   'label' => 'Type d\'équipement',       'input_type' => 'radio',   'is_required' => true,  'options_json' => ['cle_usb' => 'Clé USB', 'routeur_portable' => 'Routeur portable', 'box_fixe' => 'Box fixe', 'sim_seule' => 'Carte SIM seule'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => '5g',       'field_key' => 'data_mensuelle_go', 'label' => 'Consommation data mensuelle (Go)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 50', 'helper_text' => null],
        ],
        'telephonie-dentreprise' => [
            ['step_key' => 'entreprise', 'field_key' => 'taille_entreprise', 'label' => 'Taille de l\'entreprise', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['solo' => 'Solo / freelance', 'tpe' => 'TPE (2–9 pers.)', 'pme' => 'PME (10–249 pers.)', 'eti_ge' => 'ETI / Grande entreprise'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'entreprise', 'field_key' => 'nb_postes',         'label' => 'Nombre de postes téléphoniques', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 15', 'helper_text' => null],
            ['step_key' => 'entreprise', 'field_key' => 'integration_crm',   'label' => 'Intégration CRM nécessaire ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Salesforce, HubSpot, etc.'],
            ['step_key' => 'entreprise', 'field_key' => 'fonctionnalites',    'label' => 'Fonctionnalités requises', 'input_type' => 'checkbox', 'is_required' => false, 'options_json' => ['svi' => 'Serveur vocal interactif', 'enregistrement' => 'Enregistrement appels', 'visio' => 'Visioconférence', 'chat' => 'Chat interne', 'reporting' => 'Reporting & stats'], 'placeholder' => null, 'helper_text' => null],
        ],
        'telephone-fixe' => [
            ['step_key' => 'fixe',     'field_key' => 'type_ligne',        'label' => 'Type de ligne souhaité',   'input_type' => 'radio',   'is_required' => true,  'options_json' => ['cuivre_rng' => 'Ligne cuivre classique', 'voip_box' => 'VoIP via box internet', 'residentielle_mobile' => 'Fixe sur mobile'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'fixe',     'field_key' => 'frequence_appels',  'label' => 'Fréquence des appels',     'input_type' => 'select',  'is_required' => true,  'options_json' => ['quotidien' => 'Quotidien', 'hebdo' => 'Plusieurs fois par semaine', 'rare' => 'Rare'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'fixe',     'field_key' => 'appels_intl_fixe',  'label' => 'Appels internationaux sur fixe ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'fixe',     'field_key' => 'repondeur_avance',  'label' => 'Répondeur avancé nécessaire ?', 'input_type' => 'boolean', 'is_required' => false, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Renvoi, accueil personnalisé, boîte mail.'],
        ],
        'roaming-international' => [
            ['step_key' => 'voyage',   'field_key' => 'destinations_roaming', 'label' => 'Destinations concernées', 'input_type' => 'checkbox', 'is_required' => true, 'options_json' => ['ue' => 'UE / EEE', 'usa_canada' => 'USA / Canada', 'maghreb' => 'Maghreb', 'afrique' => 'Afrique sub-saharienne', 'asie' => 'Asie', 'amerique_latine' => 'Amérique Latine'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'voyage',   'field_key' => 'frequence_deplacement', 'label' => 'Fréquence des déplacements', 'input_type' => 'radio', 'is_required' => true, 'options_json' => ['rare' => '1–2 fois/an', 'regulier' => '3–6 fois/an', 'frequent' => '> 6 fois/an'], 'placeholder' => null, 'helper_text' => null],
            ['step_key' => 'voyage',   'field_key' => 'data_hors_ue',      'label' => 'Data hors UE (Go/déplacement)', 'input_type' => 'number', 'is_required' => false, 'options_json' => null, 'placeholder' => 'Ex : 2', 'helper_text' => null],
            ['step_key' => 'voyage',   'field_key' => 'duree_deplacement',  'label' => 'Durée moyenne des séjours', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['court' => '< 1 semaine', 'moyen' => '1–2 semaines', 'long' => '> 2 semaines'], 'placeholder' => 'Choisissez', 'helper_text' => null],
        ],
        'offre-satellite' => [
            ['step_key' => 'satellite', 'field_key' => 'raison_satellite', 'label' => 'Raison du choix satellite', 'input_type' => 'select', 'is_required' => true, 'options_json' => ['zone_blanche' => 'Zone blanche (pas de fibre/ADSL)', 'mobilite' => 'Usage nomade / mobile', 'backup' => 'Secours à une connexion existante'], 'placeholder' => 'Choisissez', 'helper_text' => null],
            ['step_key' => 'satellite', 'field_key' => 'obst_horizon',    'label' => 'Vue dégagée sur le ciel ?', 'input_type' => 'boolean', 'is_required' => true,  'options_json' => null, 'placeholder' => null, 'helper_text' => 'Nécessaire pour les systèmes GEO (Eutelsat, etc.).'],
            ['step_key' => 'satellite', 'field_key' => 'latence_critique', 'label' => 'La latence est-elle critique ?', 'input_type' => 'boolean', 'is_required' => true, 'options_json' => null, 'placeholder' => null, 'helper_text' => 'Gaming, visioconférence temps réel, trading.'],
            ['step_key' => 'satellite', 'field_key' => 'conso_data_sat',   'label' => 'Consommation mensuelle estimée (Go)', 'input_type' => 'number', 'is_required' => true, 'options_json' => null, 'placeholder' => 'Ex : 100', 'helper_text' => null],
        ],
    ];

    public function run(): void
    {
        $products = Product::query()
            ->with('sector.category')
            ->where('is_active', true)
            ->get();

        foreach ($products as $product) {
            $questionnaire = Questionnaire::query()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'name' => 'Questionnaire – '.$product->name,
                    'version' => 1,
                    'is_active' => true,
                ],
            );

            $questionnaire->questions()->delete();

            $categorySlug = $product->sector?->category?->slug ?? '';
            $sectorSlug = $product->sector?->slug ?? '';

            $baseSet = $this->baseQuestions[$categorySlug] ?? [];
            $extraSet = $this->sectorQuestions[$sectorSlug] ?? [];
            $questions = array_merge($baseSet, $extraSet);

            foreach ($questions as $order => $q) {
                /** @var array<string, mixed> $options */
                $options = $q['options_json'];

                Question::query()->create([
                    'questionnaire_id' => $questionnaire->id,
                    'step_key' => $q['step_key'],
                    'field_key' => $q['field_key'],
                    'label' => $q['label'],
                    'input_type' => QuestionInputType::from($q['input_type']),
                    'options_json' => $options,
                    'validation_rules_json' => null,
                    'display_conditions_json' => null,
                    'placeholder' => $q['placeholder'] ?? null,
                    'helper_text' => $q['helper_text'] ?? null,
                    'sort_order' => ($order + 1) * 10,
                    'is_required' => $q['is_required'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
