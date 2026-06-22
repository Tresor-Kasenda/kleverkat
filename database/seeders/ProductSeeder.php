<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * 10 produits par secteur (400 au total).
     *
     * @var array<string, array<int, array{name: string, short_description: string}>>
     */
    private array $productsBySector = [
        // ── Assurance auto ──────────────────────────────────────────────────
        'assurance-auto' => [
            ['name' => 'Auto au Tiers',                 'short_description' => 'Formule responsabilité civile, l\'essentiel au meilleur prix.'],
            ['name' => 'Auto Tiers Étendu',             'short_description' => 'Vol, incendie et bris de glace en plus du tiers.'],
            ['name' => 'Auto Tous Risques Confort',     'short_description' => 'Protection complète avec assistance 0 km.'],
            ['name' => 'Auto Tous Risques Premium',     'short_description' => 'Couverture maximale avec véhicule de remplacement.'],
            ['name' => 'Auto Jeune Conducteur',         'short_description' => 'Tarif adapté aux conducteurs novices moins de 3 ans.'],
            ['name' => 'Auto Malus',                    'short_description' => 'Assurance accessible malgré un coefficient élevé.'],
            ['name' => 'Auto Véhicule de Collection',   'short_description' => 'Garanties spécifiques pour voitures de plus de 25 ans.'],
            ['name' => 'Auto Électrique',               'short_description' => 'Couverture spéciale pour véhicules électriques et hybrides.'],
            ['name' => 'Auto Temporaire',               'short_description' => 'Assurance courte durée de 1 à 90 jours.'],
            ['name' => 'Auto Professionnel',            'short_description' => 'Formule dédiée aux véhicules à usage professionnel.'],
        ],
        // ── Assurance moto ──────────────────────────────────────────────────
        'assurance-moto' => [
            ['name' => 'Moto au Tiers',                 'short_description' => 'Responsabilité civile pour deux-roues, l\'essentiel.'],
            ['name' => 'Moto Tiers Étendu',             'short_description' => 'Vol, incendie et catastrophes naturelles en plus.'],
            ['name' => 'Moto Tous Risques',             'short_description' => 'Couverture complète moto et équipements homologués.'],
            ['name' => 'Scooter Urbain',                'short_description' => 'Formule optimisée pour l\'usage quotidien en ville.'],
            ['name' => 'Moto de Grosse Cylindrée',      'short_description' => 'Garanties renforcées pour motos > 750 cc.'],
            ['name' => 'Moto Vintage',                  'short_description' => 'Assurance pour motos de collection et oldtimers.'],
            ['name' => 'Moto Électrique',               'short_description' => 'Protection spéciale pour scooters et motos électriques.'],
            ['name' => 'Moto Jeune Conducteur',         'short_description' => 'Tarif adapté aux jeunes titulaires du permis A2.'],
            ['name' => 'Moto Compétition',              'short_description' => 'Couverture circuit et transport pour la compétition.'],
            ['name' => 'Moto Saisonnière',              'short_description' => 'Assurance 6 mois pour les motos de belle saison.'],
        ],
        // ── Assurance habitation ─────────────────────────────────────────────
        'assurance-habitation' => [
            ['name' => 'MRH Locataire Essentielle',     'short_description' => 'Garanties de base pour couvrir votre location.'],
            ['name' => 'MRH Locataire Confort',         'short_description' => 'Couverture étendue avec vol et bris de glace.'],
            ['name' => 'MRH Propriétaire Occupant',     'short_description' => 'Protection du bâtiment et des biens du propriétaire.'],
            ['name' => 'MRH Propriétaire Non Occupant', 'short_description' => 'Garanties pour biens immobiliers mis en location.'],
            ['name' => 'MRH Premium',                   'short_description' => 'Valeur à neuf, objets précieux et assistance.'],
            ['name' => 'MRH Studio & Chambre',          'short_description' => 'Formule légère pour petites surfaces étudiantes.'],
            ['name' => 'MRH Colocation',                'short_description' => 'Une seule assurance pour plusieurs colocataires.'],
            ['name' => 'MRH Résidence Secondaire',      'short_description' => 'Protection de votre logement de vacances.'],
            ['name' => 'MRH Maison Individuelle',       'short_description' => 'Couverture complète pour maisons avec jardin.'],
            ['name' => 'MRH Mobilhome & Camping',       'short_description' => 'Assurance pour résidences mobiles et mobil-homes.'],
        ],
        // ── Mutuelle santé ───────────────────────────────────────────────────
        'mutuelle-sante' => [
            ['name' => 'Mutuelle Économique Solo',      'short_description' => 'Remboursement de base pour les soins courants.'],
            ['name' => 'Mutuelle Confort Solo',         'short_description' => 'Bonne prise en charge des soins dentaires et optiques.'],
            ['name' => 'Mutuelle Premium Solo',         'short_description' => 'Remboursement renforcé médecines douces incluses.'],
            ['name' => 'Mutuelle Famille Essentielle',  'short_description' => 'Couverture de toute la famille à prix maîtrisé.'],
            ['name' => 'Mutuelle Famille Confort',      'short_description' => 'Prise en charge étendue pour les enfants et parents.'],
            ['name' => 'Mutuelle Senior Confort',       'short_description' => 'Remboursements renforcés pour les plus de 60 ans.'],
            ['name' => 'Mutuelle Senior Premium',       'short_description' => 'Optique, dentaire et appareillage auditif couverts.'],
            ['name' => 'Mutuelle Étudiant',             'short_description' => 'Formule accessible pour les étudiants de moins de 28 ans.'],
            ['name' => 'Mutuelle TNS & Indépendant',    'short_description' => 'Madelin dédié aux travailleurs non-salariés.'],
            ['name' => 'Mutuelle Entreprise',           'short_description' => 'Complémentaire collective pour les salariés.'],
        ],
        // ── Assurance emprunteur ─────────────────────────────────────────────
        'assurance-emprunteur' => [
            ['name' => 'ADI Décès Invalidité',          'short_description' => 'Garanties Décès et PTIA pour sécuriser votre prêt.'],
            ['name' => 'ADI Décès + Perte Emploi',      'short_description' => 'Protection complète avec option perte d\'emploi.'],
            ['name' => 'ADI Intégrale',                 'short_description' => 'Toutes garanties : DC, PTIA, ITT, IPP, IPT, PE.'],
            ['name' => 'ADI Senior',                    'short_description' => 'Assurance emprunteur adaptée aux plus de 55 ans.'],
            ['name' => 'ADI Non-fumeur',                'short_description' => 'Tarif réduit pour les non-fumeurs en bonne santé.'],
            ['name' => 'ADI Fonctionnaire',             'short_description' => 'Formule avantageuse pour les agents de la fonction publique.'],
            ['name' => 'ADI Co-emprunteur 50/50',       'short_description' => 'Quotité 50/50 pour protéger les deux emprunteurs.'],
            ['name' => 'ADI Co-emprunteur 100/100',     'short_description' => 'Couverture maximale à 100% sur chaque tête.'],
            ['name' => 'ADI Investissement Locatif',    'short_description' => 'Garanties optimisées pour les investisseurs immobiliers.'],
            ['name' => 'ADI Délégation Externe',        'short_description' => 'Substitution à l\'assurance groupe proposée par la banque.'],
        ],
        // ── Assurance animaux ────────────────────────────────────────────────
        'assurance-animaux' => [
            ['name' => 'Chien Essentiel',               'short_description' => 'Remboursement des soins d\'urgence pour chiens.'],
            ['name' => 'Chien Confort',                 'short_description' => 'Soins courants, vaccins et accidents couverts.'],
            ['name' => 'Chien Premium',                 'short_description' => 'Toutes maladies et chirurgies remboursées à 100%.'],
            ['name' => 'Chat Essentiel',                'short_description' => 'Couverture d\'urgence pour chats.'],
            ['name' => 'Chat Confort',                  'short_description' => 'Consultations, vaccins et hospitalisation inclus.'],
            ['name' => 'Chat Premium',                  'short_description' => 'Prise en charge complète incluant stérilisation.'],
            ['name' => 'Chiot & Chaton',                'short_description' => 'Formule adaptée aux animaux de moins de 1 an.'],
            ['name' => 'Senior Animal',                 'short_description' => 'Couverture spécifique pour animaux de plus de 8 ans.'],
            ['name' => 'NAC Petits Mammifères',         'short_description' => 'Assurance lapins, cochons d\'Inde et rongeurs.'],
            ['name' => 'Multi-animaux',                 'short_description' => 'Une seule formule pour plusieurs animaux du foyer.'],
        ],
        // ── Assurance voyage ─────────────────────────────────────────────────
        'assurance-voyage' => [
            ['name' => 'Voyage Essentiel',              'short_description' => 'Annulation et assistance médicale de base.'],
            ['name' => 'Voyage Confort',                'short_description' => 'Annulation, bagages et rapatriement sanitaire.'],
            ['name' => 'Voyage Premium',                'short_description' => 'Couverture maximale tous risques voyage.'],
            ['name' => 'Voyage Famille',                'short_description' => 'Protection complète pour toute la famille.'],
            ['name' => 'Voyage Long Séjour',            'short_description' => 'Couverture pour séjours de plus de 3 mois.'],
            ['name' => 'Voyage Business',               'short_description' => 'Assurance déplacements professionnels fréquents.'],
            ['name' => 'Voyage Monde Entier',           'short_description' => 'Abonnement annuel multi-voyages sans limite.'],
            ['name' => 'Voyage Sport & Aventure',       'short_description' => 'Garanties renforcées pour sports extrêmes.'],
            ['name' => 'Voyage Sénior',                 'short_description' => 'Formule adaptée aux voyageurs de plus de 65 ans.'],
            ['name' => 'Voyage Schengen',               'short_description' => 'Visa obligatoire pour séjour en zone Schengen.'],
        ],
        // ── Assurance scolaire ───────────────────────────────────────────────
        'assurance-scolaire' => [
            ['name' => 'Scolaire Responsabilité Civile', 'short_description' => 'RC individuelle pour les activités scolaires.'],
            ['name' => 'Scolaire Accidents',            'short_description' => 'Indemnisation des accidents corporels à l\'école.'],
            ['name' => 'Scolaire Complète',             'short_description' => 'RC + accidents + assistance scolaire à domicile.'],
            ['name' => 'Scolaire Maternelle',           'short_description' => 'Protection adaptée aux enfants de 3 à 6 ans.'],
            ['name' => 'Scolaire Primaire',             'short_description' => 'Couverture pour les élèves de CP au CM2.'],
            ['name' => 'Scolaire Collège & Lycée',      'short_description' => 'Garanties pour les adolescents de 11 à 18 ans.'],
            ['name' => 'Scolaire Sport',                'short_description' => 'Protection étendue pour les activités sportives.'],
            ['name' => 'Scolaire Extrascolaire',        'short_description' => 'Couverture en dehors des heures de classe.'],
            ['name' => 'Scolaire Périscolaire',         'short_description' => 'Garde, cantine et activités périscolaires couvertes.'],
            ['name' => 'Scolaire Internationale',       'short_description' => 'Protection pour les séjours linguistiques à l\'étranger.'],
        ],
        // ── Assurance vie ────────────────────────────────────────────────────
        'assurance-vie' => [
            ['name' => 'Assurance Vie Fonds Euro',      'short_description' => 'Capital garanti avec rendement sécurisé.'],
            ['name' => 'Assurance Vie Multi-support',   'short_description' => 'Mix fonds euro et unités de compte pour mieux performer.'],
            ['name' => 'Assurance Vie 100% UC',         'short_description' => 'Investissement dynamique en unités de compte.'],
            ['name' => 'Assurance Vie ISR',             'short_description' => 'Épargne responsable labelisée ISR et Greenfin.'],
            ['name' => 'Plan Épargne Retraite PER',     'short_description' => 'Retraite supplémentaire avec déductibilité fiscale.'],
            ['name' => 'Assurance Vie Enfant',          'short_description' => 'Épargne dédiée à l\'avenir des enfants.'],
            ['name' => 'Assurance Vie Transmission',    'short_description' => 'Optimisation fiscale pour la succession.'],
            ['name' => 'Assurance Vie Retraite Madelin', 'short_description' => 'PER individuel pour travailleurs non-salariés.'],
            ['name' => 'Assurance Vie Gestion Pilotée', 'short_description' => 'Allocation automatique selon votre profil de risque.'],
            ['name' => 'Assurance Vie Gestion Libre',   'short_description' => 'Liberté de choisir vos supports d\'investissement.'],
        ],
        // ── Prévoyance individuelle ──────────────────────────────────────────
        'prevoyance-individuelle' => [
            ['name' => 'Prévoyance Décès Essentiel',    'short_description' => 'Capital décès versé aux bénéficiaires désignés.'],
            ['name' => 'Prévoyance Décès Confort',      'short_description' => 'Capital décès + rente conjoint et orphelin.'],
            ['name' => 'Prévoyance Invalidité',         'short_description' => 'Rente mensuelle en cas d\'invalidité permanente.'],
            ['name' => 'Prévoyance Arrêt de Travail',   'short_description' => 'Indemnités journalières en cas d\'incapacité.'],
            ['name' => 'Prévoyance Complète',           'short_description' => 'Décès + invalidité + arrêt de travail en une formule.'],
            ['name' => 'Prévoyance TNS',                'short_description' => 'Protection sociale renforcée pour les indépendants.'],
            ['name' => 'Prévoyance Cadre',              'short_description' => 'Garanties haut de gamme pour cadres et dirigeants.'],
            ['name' => 'Prévoyance Parentale',          'short_description' => 'Rente éducation et capital aux enfants en cas de décès.'],
            ['name' => 'Prévoyance Maladies Graves',    'short_description' => 'Capital forfaitaire en cas de cancer, AVC ou infarctus.'],
            ['name' => 'Prévoyance Temporaire Décès',   'short_description' => 'Capital garanti pendant une période déterminée.'],
        ],

        // ── Crédit immobilier ────────────────────────────────────────────────
        'credit-immobilier' => [
            ['name' => 'Prêt Immo Taux Fixe',          'short_description' => 'Mensualités stables sur toute la durée du prêt.'],
            ['name' => 'Prêt Immo Taux Variable',       'short_description' => 'Taux révisable indexé sur l\'Euribor.'],
            ['name' => 'Prêt Immo Mixte',               'short_description' => 'Taux fixe pendant 5 ans puis variable capé.'],
            ['name' => 'Prêt à Taux Zéro PTZ',         'short_description' => 'Aide de l\'État pour les primo-accédants.'],
            ['name' => 'Prêt Accession Sociale PAS',   'short_description' => 'Financement sous conditions de ressources.'],
            ['name' => 'Prêt In Fine',                  'short_description' => 'Remboursement du capital en une seule fois à terme.'],
            ['name' => 'Prêt Relais',                   'short_description' => 'Financement court terme en attendant la vente.'],
            ['name' => 'Prêt Investissement Locatif',   'short_description' => 'Financement optimisé pour les biens mis en location.'],
            ['name' => 'Prêt Construction Maison',      'short_description' => 'Déblocage progressif des fonds selon l\'avancement.'],
            ['name' => 'Prêt Travaux',                  'short_description' => 'Financement des rénovations adossé à l\'hypothèque.'],
        ],
        // ── Crédit à la consommation ─────────────────────────────────────────
        'credit-a-la-consommation' => [
            ['name' => 'Prêt Personnel Libre',          'short_description' => 'Financement sans justificatif pour tous projets.'],
            ['name' => 'Crédit Auto Neuf',              'short_description' => 'Prêt affecté pour l\'achat d\'un véhicule neuf.'],
            ['name' => 'Crédit Auto Occasion',          'short_description' => 'Financement pour l\'achat d\'un véhicule d\'occasion.'],
            ['name' => 'Crédit Travaux',                'short_description' => 'Prêt affecté pour financer vos rénovations.'],
            ['name' => 'Crédit Vacances',               'short_description' => 'Financement de vos voyages et loisirs.'],
            ['name' => 'Crédit Renouvelable',           'short_description' => 'Réserve d\'argent disponible et reconstituable.'],
            ['name' => 'Crédit Étudiant',               'short_description' => 'Financement des études avec remboursement différé.'],
            ['name' => 'Prêt Mariage & Événements',    'short_description' => 'Financement de votre mariage ou grand événement.'],
            ['name' => 'Crédit High-Tech',              'short_description' => 'Financement de votre équipement informatique.'],
            ['name' => 'Microcrédit Personnel',         'short_description' => 'Prêt solidaire pour les personnes exclues du crédit.'],
        ],
        // ── Rachat de crédits ────────────────────────────────────────────────
        'rachat-de-credits' => [
            ['name' => 'Rachat Crédits Conso',          'short_description' => 'Regroupez vos crédits conso en une seule mensualité.'],
            ['name' => 'Rachat Crédits Immo',           'short_description' => 'Regroupement incluant un crédit immobilier.'],
            ['name' => 'Rachat Crédits Mixte',          'short_description' => 'Consolidation de crédits conso et immobilier.'],
            ['name' => 'Rachat avec Trésorerie',        'short_description' => 'Regroupement et déblocage d\'une trésorerie supplémentaire.'],
            ['name' => 'Rachat pour Propriétaires',     'short_description' => 'Consolidation avec garantie hypothécaire.'],
            ['name' => 'Rachat pour Locataires',        'short_description' => 'Regroupement sans hypothèque ni garantie immobilière.'],
            ['name' => 'Rachat Senior',                 'short_description' => 'Consolidation adaptée aux plus de 55 ans.'],
            ['name' => 'Rachat Fonctionnaire',          'short_description' => 'Conditions préférentielles pour agents publics.'],
            ['name' => 'Rachat Incidents Bancaires',    'short_description' => 'Solution pour profils avec historique difficile.'],
            ['name' => 'Rachat pour Retraités',         'short_description' => 'Consolidation avec durée adaptée aux revenus de retraite.'],
        ],
        // ── Épargne & Placements ─────────────────────────────────────────────
        'epargne-placements' => [
            ['name' => 'Livret A',                      'short_description' => 'Épargne réglementée défiscalisée et disponible.'],
            ['name' => 'Livret Développement Durable',  'short_description' => 'LDDS : épargne solidaire au même taux que le Livret A.'],
            ['name' => 'Compte Épargne Logement CEL',   'short_description' => 'CEL : épargne orientée projet immobilier.'],
            ['name' => 'Plan Épargne Logement PEL',     'short_description' => 'PEL : taux garanti et droit à un prêt immobilier.'],
            ['name' => 'Livret Épargne Populaire LEP',  'short_description' => 'LEP : taux boosté sous conditions de revenus.'],
            ['name' => 'Compte à Terme',                'short_description' => 'Capital bloqué sur une durée déterminée avec taux garanti.'],
            ['name' => 'Plan Épargne Retraite PER',     'short_description' => 'Retraite individuelle avec avantage fiscal.'],
            ['name' => 'Assurance Vie Multisupport',    'short_description' => 'Enveloppe fiscale souple pour placer sur le long terme.'],
            ['name' => 'SCPI Rendement',                'short_description' => 'Parts de sociétés immobilières à revenus réguliers.'],
            ['name' => 'Fonds en Euro Garanti',         'short_description' => 'Capital garanti avec rendement annuel sécurisé.'],
        ],
        // ── Compte bancaire ──────────────────────────────────────────────────
        'compte-bancaire' => [
            ['name' => 'Compte Courant Traditionnel',   'short_description' => 'Compte standard avec conseiller et agence.'],
            ['name' => 'Compte Banque en Ligne',        'short_description' => '100% digital, frais réduits et gestion mobile.'],
            ['name' => 'Compte Néobanque',              'short_description' => 'Application mobile first avec frais quasi nuls.'],
            ['name' => 'Compte Joint',                  'short_description' => 'Compte commun pour les couples et colocataires.'],
            ['name' => 'Compte Jeune',                  'short_description' => 'Compte adapté aux mineurs et étudiants.'],
            ['name' => 'Compte Pro Freelance',          'short_description' => 'Compte dédié aux travailleurs indépendants.'],
            ['name' => 'Compte Pro TPE',                'short_description' => 'Compte entreprise pour les très petites structures.'],
            ['name' => 'Compte sans Frais',             'short_description' => 'Zéro cotisation et services bancaires de base gratuits.'],
            ['name' => 'Compte Épargne Intégré',        'short_description' => 'Compte courant avec livret intégré à taux boosté.'],
            ['name' => 'Compte Premium',                'short_description' => 'Services exclusifs, lounge et conciergerie bancaire.'],
        ],
        // ── Carte bancaire ───────────────────────────────────────────────────
        'carte-bancaire' => [
            ['name' => 'Carte Visa Classic',            'short_description' => 'Carte débit internationale pour achats courants.'],
            ['name' => 'Carte Visa Premier',            'short_description' => 'Assurances voyage et assistance incluses.'],
            ['name' => 'Carte Visa Infinite',           'short_description' => 'Statut premium avec conciergerie et lounge.'],
            ['name' => 'Mastercard Standard',           'short_description' => 'Carte débit Mastercard acceptée partout.'],
            ['name' => 'Mastercard Gold',               'short_description' => 'Couvertures voyages et assurance automobile.'],
            ['name' => 'Carte à Débit Différé',        'short_description' => 'Débit en fin de mois pour mieux gérer son budget.'],
            ['name' => 'Carte Prépayée Rechargeable',  'short_description' => 'Sans compte bancaire, rechargeable en ligne.'],
            ['name' => 'Carte Virtuelle',               'short_description' => 'Numéro unique à usage unique pour paiements en ligne.'],
            ['name' => 'Carte 0% Frais Étranger',      'short_description' => 'Zéro commission sur les transactions en devises.'],
            ['name' => 'Carte Cashback',                'short_description' => 'Remboursement d\'un pourcentage de vos achats.'],
        ],
        // ── Crédit auto ──────────────────────────────────────────────────────
        'credit-auto' => [
            ['name' => 'Prêt Auto Taux Fixe',          'short_description' => 'Financement classique avec mensualités constantes.'],
            ['name' => 'LOA Location avec Option',      'short_description' => 'Location longue durée avec option d\'achat finale.'],
            ['name' => 'LLD Location sans Option',      'short_description' => 'Location pure sans option d\'achat.'],
            ['name' => 'Prêt Auto Véhicule Neuf',      'short_description' => 'Crédit constructeur ou banque pour neuf.'],
            ['name' => 'Prêt Auto Véhicule Occasion',  'short_description' => 'Financement pour l\'achat d\'un véhicule d\'occasion.'],
            ['name' => 'Prêt Auto Électrique',         'short_description' => 'Financement avantageux pour véhicules électriques.'],
            ['name' => 'Prêt Auto Utilitaire',         'short_description' => 'Financement pour fourgonnettes et utilitaires.'],
            ['name' => 'Prêt Auto Moto',               'short_description' => 'Crédit deux-roues motorisé avec taux compétitif.'],
            ['name' => 'Prêt Auto Sans Apport',        'short_description' => 'Financement à 100% sans apport initial.'],
            ['name' => 'Prêt Auto Ballon',             'short_description' => 'Mensualités basses avec solde à payer en fin de contrat.'],
        ],
        // ── Prêt étudiant ────────────────────────────────────────────────────
        'pret-etudiant' => [
            ['name' => 'Prêt Étudiant Sans Caution',   'short_description' => 'Crédit garanti par l\'État sans garant personnel.'],
            ['name' => 'Prêt Étudiant Avec Caution',   'short_description' => 'Taux réduit avec caution parentale ou bancaire.'],
            ['name' => 'Prêt Études Supérieures',      'short_description' => 'Financement global cursus Bac+3 à Bac+5.'],
            ['name' => 'Prêt Master & Grande École',   'short_description' => 'Financement spécifique aux masters et grandes écoles.'],
            ['name' => 'Prêt Médecine & Pharmacie',    'short_description' => 'Financement des longues études médicales.'],
            ['name' => 'Prêt MBA & Doctorat',          'short_description' => 'Financement des études post-bac avancées.'],
            ['name' => 'Prêt Étudiant International',  'short_description' => 'Financement des études à l\'étranger.'],
            ['name' => 'Prêt Apprentissage',           'short_description' => 'Complément de financement pour apprentis.'],
            ['name' => 'Prêt Formation Professionnelle', 'short_description' => 'Financement des reconversions et formations adultes.'],
            ['name' => 'Prêt Étudiant Taux Zéro',     'short_description' => 'Aide régionale ou partenariale sans intérêts.'],
        ],
        // ── Bourse & Investissement ──────────────────────────────────────────
        'bourse-investissement' => [
            ['name' => 'PEA Plan Épargne Action',       'short_description' => 'Enveloppe fiscale pour actions européennes.'],
            ['name' => 'Compte Titres Ordinaire',       'short_description' => 'Liberté totale d\'investissement sans plafond.'],
            ['name' => 'PEA PME',                       'short_description' => 'Investissement dans les PME et ETI françaises.'],
            ['name' => 'Courtier en Ligne Actif',       'short_description' => 'Plateforme avancée pour traders actifs.'],
            ['name' => 'Gestion Pilotée ETF',           'short_description' => 'Portefeuille automatisé en ETF low-cost.'],
            ['name' => 'Crowdfunding Immobilier',       'short_description' => 'Investissement participatif dans des projets immobiliers.'],
            ['name' => 'FCPI FCPR Capital Risque',      'short_description' => 'Fonds d\'investissement dans les start-ups.'],
            ['name' => 'Or & Métaux Précieux',          'short_description' => 'Investissement en or physique ou papier.'],
            ['name' => 'Crypto ETF Bitcoin',            'short_description' => 'Exposition réglementée aux cryptomonnaies via ETF.'],
            ['name' => 'Robo-Advisor Gestion Libre',    'short_description' => 'Conseiller algorithmique pour portefeuille diversifié.'],
        ],
        // ── Assurance emprunteur banque ──────────────────────────────────────
        'assurance-emprunteur-banque' => [
            ['name' => 'Délégation Assurance Basique',  'short_description' => 'Substitution minimaliste à l\'assurance groupe.'],
            ['name' => 'Délégation Assurance Confort',  'short_description' => 'Garanties DC + PTIA + ITT à tarif compétitif.'],
            ['name' => 'Délégation Assurance Premium',  'short_description' => 'Toutes garanties avec option perte d\'emploi.'],
            ['name' => 'Assurance Groupe Bancaire',     'short_description' => 'Offre standard proposée par votre banque prêteuse.'],
            ['name' => 'Délégation Jeune Emprunteur',   'short_description' => 'Tarif optimisé pour les emprunteurs de moins de 40 ans.'],
            ['name' => 'Délégation Senior',             'short_description' => 'Formule adaptée pour les emprunteurs de plus de 55 ans.'],
            ['name' => 'Délégation Pro Libéral',        'short_description' => 'Protection adaptée aux professions libérales.'],
            ['name' => 'Délégation Sportif',            'short_description' => 'Garanties pour pratiquants de sports à risques.'],
            ['name' => 'Rachat Délégation Assurance',   'short_description' => 'Substitution d\'assurance en cours de prêt.'],
            ['name' => 'Comparatif Groupes vs Délégation', 'short_description' => 'Analyse comparative pour choisir la meilleure offre.'],
        ],

        // ── Électricité ──────────────────────────────────────────────────────
        'electricite' => [
            ['name' => 'Électricité Prix Fixe',         'short_description' => 'Tarif du kWh bloqué de 1 à 3 ans.'],
            ['name' => 'Électricité Indexée TRV',       'short_description' => 'Prix indexé sur le tarif réglementé EDF.'],
            ['name' => 'Électricité Verte Certifiée',   'short_description' => 'Certificats d\'énergie renouvelable garantis.'],
            ['name' => 'Électricité Heure Creuse',      'short_description' => 'Option HC/HP pour optimiser selon l\'horaire.'],
            ['name' => 'Électricité Tempo',             'short_description' => 'Tarif variable selon les jours bleu/blanc/rouge.'],
            ['name' => 'Électricité ZEV',               'short_description' => 'Offre spéciale pour recharge de véhicule électrique.'],
            ['name' => 'Électricité Professionnel',     'short_description' => 'Offres business pour commerces et TPE.'],
            ['name' => 'Électricité Sans Engagement',   'short_description' => 'Contrat résiliable à tout moment sans frais.'],
            ['name' => 'Électricité Budget Maîtrisé',   'short_description' => 'Mensualisation lissée pour éviter les surprises.'],
            ['name' => 'Électricité 100% Nucléaire',    'short_description' => 'Énergie bas-carbone d\'origine nucléaire française.'],
        ],
        // ── Gaz naturel ──────────────────────────────────────────────────────
        'gaz-naturel' => [
            ['name' => 'Gaz Prix Fixe',                 'short_description' => 'Tarif du MWh bloqué pour une durée déterminée.'],
            ['name' => 'Gaz Indexé TRVg',               'short_description' => 'Prix indexé sur le tarif réglementé Engie.'],
            ['name' => 'Gaz Vert Biométhane',           'short_description' => 'Injection de biométhane d\'origine locale.'],
            ['name' => 'Gaz Chauffage Collectif',       'short_description' => 'Offre pour immeubles avec chauffage central gaz.'],
            ['name' => 'Gaz Sans Engagement',           'short_description' => 'Contrat résiliable sans frais à tout moment.'],
            ['name' => 'Gaz Budget Mensuel',            'short_description' => 'Mensualité fixe adaptée à votre consommation.'],
            ['name' => 'Gaz Professionnel',             'short_description' => 'Fourniture gaz pour les professionnels et TPE.'],
            ['name' => 'Gaz Géothermique Assisté',      'short_description' => 'Complément gaz pour installations hybrides.'],
            ['name' => 'Gaz Économie Garantie',         'short_description' => 'Engagement de réduction par rapport à votre facture actuelle.'],
            ['name' => 'Gaz & Services Inclus',         'short_description' => 'Fourniture avec maintenance chaudière incluse.'],
        ],
        // ── Électricité & Gaz ────────────────────────────────────────────────
        'electricite-gaz' => [
            ['name' => 'Duo Élec + Gaz Prix Fixe',     'short_description' => 'Double énergie avec tarif bloqué avantageux.'],
            ['name' => 'Duo Élec + Gaz Vert',          'short_description' => 'Énergie renouvelable sur les deux sources.'],
            ['name' => 'Duo Sans Engagement',           'short_description' => 'Combo résiliable à tout moment.'],
            ['name' => 'Duo Maison Ancienne',           'short_description' => 'Offre adaptée aux logements peu isolés.'],
            ['name' => 'Duo Maison BBC',                'short_description' => 'Offre optimisée pour les bâtiments basse consommation.'],
            ['name' => 'Duo avec Diagnostic Éco',       'short_description' => 'Conseils personnalisés sur votre consommation.'],
            ['name' => 'Duo Budget Maîtrisé',           'short_description' => 'Mensualité unique pour électricité et gaz.'],
            ['name' => 'Duo Locataire',                 'short_description' => 'Changement simplifié pour les locataires.'],
            ['name' => 'Duo Propriétaire',              'short_description' => 'Formule premium avec services dépannage.'],
            ['name' => 'Duo Professionnel',             'short_description' => 'Double énergie pour commerces et bureaux.'],
        ],
        // ── Énergie verte ────────────────────────────────────────────────────
        'energie-verte' => [
            ['name' => 'Éolien Offshore',               'short_description' => 'Électricité issue d\'éoliennes en mer.'],
            ['name' => 'Hydraulique Garanti',           'short_description' => 'Énergie produite par les barrages français.'],
            ['name' => 'Solaire Photovoltaïque',        'short_description' => 'Électricité issue de panneaux solaires locaux.'],
            ['name' => 'Mix Renouvelable Certifié',     'short_description' => 'Garanties d\'origine multi-sources vérifiées.'],
            ['name' => 'Biogaz Territorial',            'short_description' => 'Biométhane produit localement à partir de déchets.'],
            ['name' => 'Autoconsommation Collective',   'short_description' => 'Partage de production solaire en communauté.'],
            ['name' => 'Énergie Verte + Bilan Carbone', 'short_description' => 'Compensation carbone intégrée à l\'offre.'],
            ['name' => 'Énergie Locale Producteur',     'short_description' => 'Approvisionnement direct auprès de producteurs locaux.'],
            ['name' => 'Énergie Verte Pro',             'short_description' => 'Offre professionnelle avec reporting RSE.'],
            ['name' => 'Contrat Power Purchase PPA',    'short_description' => 'Achat direct de production renouvelable long terme.'],
        ],
        // ── Panneaux solaires ─────────────────────────────────────────────────
        'panneaux-solaires' => [
            ['name' => 'Kit Solaire 3 kWc',             'short_description' => 'Installation résidentielle idéale pour petite maison.'],
            ['name' => 'Kit Solaire 6 kWc',             'short_description' => 'Installation pour maison 4 personnes avec piscine.'],
            ['name' => 'Kit Solaire 9 kWc',             'short_description' => 'Grande installation pour forte consommation.'],
            ['name' => 'Panneaux Monocristallins',      'short_description' => 'Meilleur rendement pour toitures avec espace limité.'],
            ['name' => 'Panneaux Polycristallins',      'short_description' => 'Solution économique à bon rendement global.'],
            ['name' => 'Panneaux Bifaciaux',            'short_description' => 'Captent la lumière des deux côtés pour plus d\'énergie.'],
            ['name' => 'Solaire + Batterie de Stockage', 'short_description' => 'Autoconsommation optimisée avec stockage.'],
            ['name' => 'Solaire + Borne de Recharge',   'short_description' => 'Recharger son véhicule électrique avec l\'énergie du soleil.'],
            ['name' => 'Solaire Thermique Eau Chaude',  'short_description' => 'Chauffe-eau solaire pour eau chaude sanitaire.'],
            ['name' => 'Solaire Intégration Toiture',   'short_description' => 'Tuiles solaires intégrées à la toiture.'],
        ],
        // ── Pompe à chaleur ──────────────────────────────────────────────────
        'pompe-a-chaleur' => [
            ['name' => 'PAC Air/Air Monosplit',         'short_description' => 'Climatisation réversible pour une pièce.'],
            ['name' => 'PAC Air/Air Multisplit',        'short_description' => 'Climatisation réversible pour plusieurs pièces.'],
            ['name' => 'PAC Air/Eau Chauffage',         'short_description' => 'Chauffage central via radiateurs ou plancher.'],
            ['name' => 'PAC Géothermique Sol/Eau',      'short_description' => 'Captage horizontal pour grand terrain.'],
            ['name' => 'PAC Eau/Eau Nappe',             'short_description' => 'Captage sur nappe phréatique très performant.'],
            ['name' => 'PAC Hybride Gaz',               'short_description' => 'Couplage pompe à chaleur et chaudière gaz.'],
            ['name' => 'PAC Haute Température',         'short_description' => 'Compatible avec les anciens radiateurs fonte.'],
            ['name' => 'PAC + Ballon Thermodynamique',  'short_description' => 'Chauffage et eau chaude sanitaire combinés.'],
            ['name' => 'PAC Petite Surface',            'short_description' => 'Solution compacte pour appartements et studios.'],
            ['name' => 'PAC Connectée',                 'short_description' => 'Pilotage à distance et optimisation via appli.'],
        ],
        // ── Chaudière ────────────────────────────────────────────────────────
        'chaudiere' => [
            ['name' => 'Chaudière Gaz Condensation',    'short_description' => 'Meilleur rendement thermique du marché gaz.'],
            ['name' => 'Chaudière Gaz Basse Température', 'short_description' => 'Remplacement économique de chaudière ancienne.'],
            ['name' => 'Chaudière Fioul',               'short_description' => 'Pour les zones non raccordées au gaz de ville.'],
            ['name' => 'Chaudière Bois Granulés',       'short_description' => 'Poêle-chaudière pellets pour biocombustible local.'],
            ['name' => 'Chaudière Mixte Gaz & Pellets', 'short_description' => 'Flexibilité entre deux énergies complémentaires.'],
            ['name' => 'Chaudière Murale Compacte',     'short_description' => 'Installation dans un petit espace.'],
            ['name' => 'Chaudière Au Sol',              'short_description' => 'Haute puissance pour grandes surfaces.'],
            ['name' => 'Chaudière Micro-cogénération',  'short_description' => 'Produit chaleur et électricité simultanément.'],
            ['name' => 'Chaudière Connectée',           'short_description' => 'Thermostat intelligent et suivi de consommation.'],
            ['name' => 'Remplacement Chaudière Urgence', 'short_description' => 'Installation rapide avec prise en charge prioritaire.'],
        ],
        // ── Isolation thermique ──────────────────────────────────────────────
        'isolation-thermique' => [
            ['name' => 'Isolation Combles Perdus',      'short_description' => 'Soufflage laine minérale en combles non aménagés.'],
            ['name' => 'Isolation Rampants Toiture',    'short_description' => 'Isolation sous toiture inclinée habitable.'],
            ['name' => 'Isolation Murs Intérieure',     'short_description' => 'Doublage intérieur des murs pour logements collectifs.'],
            ['name' => 'Isolation Murs Extérieure ITE', 'short_description' => 'Bardage isolant par l\'extérieur.'],
            ['name' => 'Isolation Plancher Bas',        'short_description' => 'Isolation du sol sur vide sanitaire ou garage.'],
            ['name' => 'Isolation Fenêtres Double Vitrage', 'short_description' => 'Remplacement des menuiseries single pour double vitrage.'],
            ['name' => 'Isolation Fenêtres Triple Vitrage', 'short_description' => 'Performance maximale pour les régions froides.'],
            ['name' => 'Isolation Maison Passive',      'short_description' => 'Enveloppe ultra-performante label passif.'],
            ['name' => 'Audit + Isolation Globale',     'short_description' => 'Diagnostic et rénovation thermique complète.'],
            ['name' => 'Isolation Laine de Bois',       'short_description' => 'Isolant naturel biosourcé haute performance.'],
        ],
        // ── Ballon thermodynamique ────────────────────────────────────────────
        'ballon-thermodynamique' => [
            ['name' => 'Ballon 200 L Air Ambiant',      'short_description' => 'Idéal pour un foyer de 2 à 3 personnes.'],
            ['name' => 'Ballon 300 L Air Ambiant',      'short_description' => 'Adapté à une famille de 4 à 5 personnes.'],
            ['name' => 'Ballon 200 L Air Extérieur',    'short_description' => 'Captage sur l\'air extérieur, efficacité toute l\'année.'],
            ['name' => 'Ballon Solaire Hybride',        'short_description' => 'Couplage thermodynamique et appoint solaire.'],
            ['name' => 'Ballon Connecté WiFi',          'short_description' => 'Programmation via smartphone et suivi en temps réel.'],
            ['name' => 'Ballon Vertical Gainable',      'short_description' => 'Installation sur gaine de ventilation existante.'],
            ['name' => 'Ballon Split Séparé',           'short_description' => 'Unité de pompe à chaleur déportée à l\'extérieur.'],
            ['name' => 'Ballon Monobloc Compact',       'short_description' => 'Tout-en-un pour petites salles de bains.'],
            ['name' => 'Ballon Haute Performance COP 4', 'short_description' => 'Coefficient de performance exceptionnel.'],
            ['name' => 'Remplacement Chauffe-eau Électrique', 'short_description' => 'Passage de résistance à thermodynamique.'],
        ],
        // ── Audit énergétique ─────────────────────────────────────────────────
        'audit-energetique' => [
            ['name' => 'DPE Diagnostic de Performance', 'short_description' => 'Étiquette énergie obligatoire pour vente et location.'],
            ['name' => 'Audit Réglementaire',           'short_description' => 'Audit obligatoire avant gros travaux de rénovation.'],
            ['name' => 'Audit Global Maison',           'short_description' => 'Diagnostic complet avec plan de travaux priorisé.'],
            ['name' => 'Audit Copropriété',             'short_description' => 'Audit collectif pour immeuble en copropriété.'],
            ['name' => 'Audit Thermique Infrarouge',    'short_description' => 'Détection des ponts thermiques par caméra.'],
            ['name' => 'Bilan Énergie + Aides',         'short_description' => 'Calcul des aides disponibles MaPrimeRénov\'.'],
            ['name' => 'Audit Bâtiment Professionnel',  'short_description' => 'Diagnostic des locaux commerciaux et tertiaires.'],
            ['name' => 'Audit + Simulation Économies',  'short_description' => 'Projection des économies après travaux.'],
            ['name' => 'Thermographie Aérienne Drone',  'short_description' => 'Cartographie des pertes thermiques par drone.'],
            ['name' => 'Accompagnement Mon Accompagnateur Rénov', 'short_description' => 'Aide à l\'élaboration du projet et au suivi.'],
        ],

        // ── Forfait mobile ───────────────────────────────────────────────────
        'forfait-mobile' => [
            ['name' => 'Forfait 5 Go Sans Engagement',  'short_description' => 'Petit volume idéal pour utilisation légère.'],
            ['name' => 'Forfait 20 Go Sans Engagement', 'short_description' => 'Usage courant réseaux sociaux et navigation.'],
            ['name' => 'Forfait 50 Go Sans Engagement', 'short_description' => 'Streaming vidéo et appels visio confortables.'],
            ['name' => 'Forfait 100 Go 5G',             'short_description' => 'Très haut débit mobile pour usage intensif.'],
            ['name' => 'Forfait Illimité 5G',           'short_description' => 'Data illimitée en 5G pour les grands consommateurs.'],
            ['name' => 'Forfait Senior',                'short_description' => 'Téléphonie simple et assistance prioritaire.'],
            ['name' => 'Forfait Enfant',                'short_description' => 'Contrôle parental et volume limité pour les ados.'],
            ['name' => 'Forfait Pro 5G',                'short_description' => 'Usage professionnel avec roaming inclus.'],
            ['name' => 'Forfait International',         'short_description' => 'Appels et data vers plus de 100 destinations.'],
            ['name' => 'Forfait avec Smartphone',       'short_description' => 'Nouveau téléphone inclus avec abonnement 24 mois.'],
        ],
        // ── Box internet ─────────────────────────────────────────────────────
        'box-internet' => [
            ['name' => 'Box ADSL Essentielle',          'short_description' => 'Internet ADSL pour les zones non fibrées.'],
            ['name' => 'Box Fibre 500 Mb',              'short_description' => 'Fibre symétrique idéale pour 3 à 4 utilisateurs.'],
            ['name' => 'Box Fibre 1 Gb',                'short_description' => 'Très haut débit pour familles nombreuses.'],
            ['name' => 'Box Fibre 2 Gb',                'short_description' => 'Ultra haut débit pour gamers et télétravailleurs.'],
            ['name' => 'Box TV Incluse',                'short_description' => 'Fibre + bouquet TV + téléphonie fixe illimitée.'],
            ['name' => 'Box 4G Secours',                'short_description' => 'Internet 4G en attendant la fibre.'],
            ['name' => 'Box Sans TV',                   'short_description' => 'Fibre pure sans option télévision.'],
            ['name' => 'Box Pro PME',                   'short_description' => 'Connexion fibre professionnelle avec SLA garanti.'],
            ['name' => 'Box Sans Engagement',           'short_description' => 'Fibre résiliable à tout moment.'],
            ['name' => 'Box avec Options Premium',      'short_description' => 'Multiroom 4K, sport et cinéma inclus.'],
        ],
        // ── Fibre optique ─────────────────────────────────────────────────────
        'fibre-optique' => [
            ['name' => 'Fibre FTTH 300 Mb',            'short_description' => 'Fibre jusqu\'à l\'abonné 300 Mb symétrique.'],
            ['name' => 'Fibre FTTH 500 Mb',            'short_description' => 'Débit idéal pour le télétravail HD.'],
            ['name' => 'Fibre FTTH 1 Gb',              'short_description' => 'Gigabit symétrique pour foyers connectés.'],
            ['name' => 'Fibre FTTH 2 Gb',              'short_description' => 'Débit ultime pour les utilisateurs exigeants.'],
            ['name' => 'Fibre FTTB Immeuble',          'short_description' => 'Fibre jusqu\'à l\'immeuble pour collectifs.'],
            ['name' => 'Fibre Pro Garanti',             'short_description' => 'SLA 99,9% et intervention sous 4h.'],
            ['name' => 'Fibre Symétrique Pro',         'short_description' => 'Même débit montant et descendant.'],
            ['name' => 'Fibre + VoIP Pro',             'short_description' => 'Ligne fibre et standard IP intégrés.'],
            ['name' => 'Fibre Noire Dédiée',           'short_description' => 'Infrastructure dédiée pour grandes entreprises.'],
            ['name' => 'Fibre + Backup 4G',            'short_description' => 'Continuité de service avec bascule 4G automatique.'],
        ],
        // ── ADSL ─────────────────────────────────────────────────────────────
        'adsl' => [
            ['name' => 'ADSL 8 Mb Basique',            'short_description' => 'Connexion ADSL pour navigation légère.'],
            ['name' => 'ADSL 20 Mb Standard',          'short_description' => 'Débit standard pour usage courant.'],
            ['name' => 'ADSL+ 30 Mb',                  'short_description' => 'Débit amélioré avec profil ADSL2+.'],
            ['name' => 'VDSL2 50 Mb',                  'short_description' => 'Très haut débit sur lignes courtes.'],
            ['name' => 'ADSL avec TV',                 'short_description' => 'Triple play avec bouquet TV inclus.'],
            ['name' => 'ADSL Sans Téléphone Fixe',     'short_description' => 'Internet seul sans ligne téléphonique.'],
            ['name' => 'ADSL Pro Petite Entreprise',   'short_description' => 'ADSL professionnel avec assistance dédiée.'],
            ['name' => 'ADSL Illimité Soir & Week-End', 'short_description' => 'Haut débit hors heures creuses illimité.'],
            ['name' => 'ADSL + 4G Backup',             'short_description' => 'Internet ADSL renforcé par 4G en secours.'],
            ['name' => 'ADSL Zone Blanche',            'short_description' => 'Solution pour les zones non couvertes autrement.'],
        ],
        // ── Triple play ───────────────────────────────────────────────────────
        'triple-play' => [
            ['name' => 'Triple Play Essentiel',         'short_description' => 'Fibre + fixe + TV de base à prix mini.'],
            ['name' => 'Triple Play Confort',           'short_description' => 'Décodeur HD, téléphonie + 100 chaînes.'],
            ['name' => 'Triple Play Premium',           'short_description' => 'Fibre 1 Gb, 200 chaînes et replay 7 jours.'],
            ['name' => 'Triple Play Sport',             'short_description' => 'Bouquets sportifs Canal+, BeIN et RMC inclus.'],
            ['name' => 'Triple Play Cinéma',            'short_description' => 'VOD, Netflix et Disney+ inclus.'],
            ['name' => 'Triple Play Famille',           'short_description' => 'Multi-décodeur et contrôle parental avancé.'],
            ['name' => 'Triple Play Senior',            'short_description' => 'Interface simplifiée et assistance prioritaire.'],
            ['name' => 'Triple Play Pro',               'short_description' => 'Connexion pro + standard VoIP et TV.'],
            ['name' => 'Triple Play Gaming',            'short_description' => 'Faible latence et priorisation gaming.'],
            ['name' => 'Triple Play Sans Engagement',   'short_description' => 'Résiliable à tout moment sans pénalités.'],
        ],
        // ── Internet mobile 5G ────────────────────────────────────────────────
        'internet-mobile-5g' => [
            ['name' => 'Clé 4G Légère',                'short_description' => 'Clé USB 4G pour ordinateur portable.'],
            ['name' => 'Routeur 4G Portable',          'short_description' => 'Hotspot WiFi mobile 4G jusqu\'à 10 appareils.'],
            ['name' => 'Routeur 5G Portable',          'short_description' => 'WiFi 5G ultra rapide en déplacement.'],
            ['name' => 'Box 4G Fixe Intérieure',       'short_description' => 'Box 4G à poser chez soi pour zones sans fibre.'],
            ['name' => 'Box 5G Fixe',                  'short_description' => 'Internet très haut débit 5G en alternative fibre.'],
            ['name' => 'Data SIM Pro',                 'short_description' => 'SIM data seule pour tablette ou PC.'],
            ['name' => 'Internet Mobile Illimité',     'short_description' => 'Data illimitée sans restriction de vitesse.'],
            ['name' => 'Box 4G Extérieure',            'short_description' => 'Antenne extérieure pour zones à faible signal.'],
            ['name' => 'Internet Satellite LEO',       'short_description' => 'Internet satellitaire basse orbite très réactif.'],
            ['name' => 'eSIM International',           'short_description' => 'SIM virtuelle activable dans plus de 150 pays.'],
        ],
        // ── Téléphonie d'entreprise ───────────────────────────────────────────
        'telephonie-dentreprise' => [
            ['name' => 'Standard IP Cloud',            'short_description' => 'Standard téléphonique virtuel hébergé dans le cloud.'],
            ['name' => 'Trunk SIP',                    'short_description' => 'Lignes SIP pour PABX IP existant.'],
            ['name' => 'Numéros Non Géographiques 08X', 'short_description' => 'Numéros spéciaux pour service client.'],
            ['name' => 'Click to Call',                'short_description' => 'Appel depuis navigateur en un clic.'],
            ['name' => 'Softphone Entreprise',         'short_description' => 'Application de téléphonie sur PC et mobile.'],
            ['name' => 'UCaaS Collaboration',         'short_description' => 'Voix, vidéo, chat et partage en une seule plateforme.'],
            ['name' => 'Téléconférence Entreprise',    'short_description' => 'Ponts de conférence multi-participants.'],
            ['name' => 'Enregistrement Appels',        'short_description' => 'Archivage et réécoute des conversations.'],
            ['name' => 'Centre d\'Appels CCaaS',       'short_description' => 'Solution call center cloud scalable.'],
            ['name' => 'Numéros Internationaux',       'short_description' => 'Présence locale dans plus de 50 pays.'],
        ],
        // ── Téléphone fixe ────────────────────────────────────────────────────
        'telephone-fixe' => [
            ['name' => 'Ligne Analogique RTC',         'short_description' => 'Ligne téléphonique cuivre classique.'],
            ['name' => 'Ligne VoIP Résidentielle',     'short_description' => 'Téléphonie sur IP incluse dans la box.'],
            ['name' => 'Ligne RNIS ISDN',              'short_description' => 'Ligne numérique pour télécopie et données.'],
            ['name' => 'Téléphone Fixe Sans Abonnement', 'short_description' => 'Appareil prépayé sans abonnement mensuel.'],
            ['name' => 'Ligne Fixe Pro Garantie',      'short_description' => 'SLA et assistance prioritaire pour professionnels.'],
            ['name' => 'Ligne Fixe 4G Backup',         'short_description' => 'Ligne fixe avec continuité via 4G.'],
            ['name' => 'Ligne Fixe Internationale',    'short_description' => 'Appels internationaux illimités inclus.'],
            ['name' => 'Téléphone Fixe Senior',        'short_description' => 'Appareil simplifié avec grosses touches.'],
            ['name' => 'Numéro Portable Fixe',         'short_description' => 'Numéro géographique sur mobile.'],
            ['name' => 'Ligne Fixe avec Répondeur',    'short_description' => 'Messagerie vocale illimitée incluse.'],
        ],
        // ── Roaming international ─────────────────────────────────────────────
        'roaming-international' => [
            ['name' => 'Pass UE Inclus',               'short_description' => 'Utilisation de son forfait dans l\'UE sans surcoût.'],
            ['name' => 'Pass Monde 1 Go',              'short_description' => '1 Go de data hors UE à tarif forfaitaire.'],
            ['name' => 'Pass Monde 5 Go',              'short_description' => '5 Go en itinérance pour séjours prolongés.'],
            ['name' => 'Pass Monde Illimité Voix',     'short_description' => 'Appels illimités depuis l\'étranger.'],
            ['name' => 'Pass Amérique du Nord',        'short_description' => 'États-Unis, Canada, Mexique inclus.'],
            ['name' => 'Pass Asie Pacifique',          'short_description' => 'Couverture des principaux pays d\'Asie.'],
            ['name' => 'Pass Afrique',                 'short_description' => 'Roaming pour 30 pays d\'Afrique.'],
            ['name' => 'Pass Week-End Étranger',       'short_description' => 'Forfait court séjour 48h à l\'étranger.'],
            ['name' => 'SIM Internationale Prépayée',  'short_description' => 'Carte SIM multi-pays rechargeable.'],
            ['name' => 'eSIM Voyage',                  'short_description' => 'SIM virtuelle activable avant le départ.'],
        ],
        // ── Offre satellite ───────────────────────────────────────────────────
        'offre-satellite' => [
            ['name' => 'Satellite Basique 20 Mb',      'short_description' => 'Internet par satellite pour zones blanches.'],
            ['name' => 'Satellite Confort 50 Mb',      'short_description' => 'Streaming et télétravail en zone rurale.'],
            ['name' => 'Satellite 100 Mb+',            'short_description' => 'Haut débit satellite pour usage intensif.'],
            ['name' => 'LEO Très Basse Latence',       'short_description' => 'Satellite basse orbite avec latence < 50 ms.'],
            ['name' => 'Satellite Pro Entreprise',     'short_description' => 'Connexion satellite avec SLA professionnel.'],
            ['name' => 'Satellite Maritime',           'short_description' => 'Connexion pour bateaux et yachts.'],
            ['name' => 'Satellite Nomade',             'short_description' => 'Connexion déplaçable entre sites distants.'],
            ['name' => 'Satellite IoT M2M',            'short_description' => 'Connectivité pour objets connectés en zones isolées.'],
            ['name' => 'Satellite 4G Hybride',         'short_description' => 'Bascule intelligente satellite / 4G.'],
            ['name' => 'Offre Starlink Résidentiel',   'short_description' => 'Service LEO très performant en zone blanche.'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->productsBySector as $sectorSlug => $products) {
            $sector = Sector::query()->where('slug', $sectorSlug)->first();

            if ($sector === null) {
                $this->command?->warn("Secteur introuvable : {$sectorSlug}");

                continue;
            }

            foreach ($products as $index => $data) {
                $slug = Str::slug($data['name']).'-'.Str::slug($sector->name);

                Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'sector_id' => $sector->id,
                        'name' => $data['name'],
                        'short_description' => $data['short_description'],
                        'description' => $data['short_description'],
                        'sort_order' => ($index + 1) * 10,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
