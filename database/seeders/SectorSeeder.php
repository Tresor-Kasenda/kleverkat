<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectorSeeder extends Seeder
{
    /** @var array<string, array<int, array{name: string, description: string}>> */
    private array $sectorsByCategory = [
        'Assurance' => [
            ['name' => 'Assurance auto',            'description' => 'Comparez les formules au tiers, intermédiaires et tous risques pour votre voiture.'],
            ['name' => 'Assurance moto',            'description' => 'Couverture deux-roues pour motos, scooters et cyclomoteurs.'],
            ['name' => 'Assurance habitation',      'description' => 'Multirisque habitation pour locataires, propriétaires et colocations.'],
            ['name' => 'Mutuelle santé',            'description' => 'Complémentaires santé individuelles, familiales et pour seniors.'],
            ['name' => 'Assurance emprunteur',      'description' => 'Assurance de prêt pour sécuriser votre crédit immobilier.'],
            ['name' => 'Assurance animaux',         'description' => 'Remboursement des frais vétérinaires pour chiens et chats.'],
            ['name' => 'Assurance voyage',          'description' => 'Protection pendant vos déplacements en France et à l\'étranger.'],
            ['name' => 'Assurance scolaire',        'description' => 'Couverture responsabilité civile et accidents pour les enfants.'],
            ['name' => 'Assurance vie',             'description' => 'Produits d\'épargne et de prévoyance à long terme.'],
            ['name' => 'Prévoyance individuelle',   'description' => 'Garantie décès, invalidité et incapacité de travail.'],
        ],
        'Crédit & Banque' => [
            ['name' => 'Crédit immobilier',         'description' => 'Financement de l\'achat d\'un bien immobilier neuf ou ancien.'],
            ['name' => 'Crédit à la consommation',  'description' => 'Prêt personnel et crédit renouvelable pour vos projets.'],
            ['name' => 'Rachat de crédits',         'description' => 'Regroupement de crédits pour alléger vos mensualités.'],
            ['name' => 'Épargne & Placements',      'description' => 'Livrets, PEL, assurance vie et placements boursiers.'],
            ['name' => 'Compte bancaire',           'description' => 'Comptes courants, banques en ligne et néobanques.'],
            ['name' => 'Carte bancaire',            'description' => 'Cartes de débit, crédit, premium et sans frais à l\'étranger.'],
            ['name' => 'Crédit auto',               'description' => 'Financement de l\'achat d\'un véhicule neuf ou d\'occasion.'],
            ['name' => 'Prêt étudiant',             'description' => 'Financement des études supérieures avec taux préférentiels.'],
            ['name' => 'Bourse & Investissement',   'description' => 'PEA, CTO et courtiers en ligne pour investir en bourse.'],
            ['name' => 'Assurance emprunteur banque', 'description' => 'Délégation d\'assurance pour vos crédits bancaires.'],
        ],
        'Énergie' => [
            ['name' => 'Électricité',               'description' => 'Offres de fourniture d\'électricité à prix fixe ou variable.'],
            ['name' => 'Gaz naturel',               'description' => 'Offres de fourniture de gaz naturel pour particuliers.'],
            ['name' => 'Électricité & Gaz',         'description' => 'Offres combinées énergie pour simplifier vos contrats.'],
            ['name' => 'Énergie verte',             'description' => 'Électricité et gaz d\'origine 100% renouvelable.'],
            ['name' => 'Panneaux solaires',         'description' => 'Installation photovoltaïque et autoconsommation.'],
            ['name' => 'Pompe à chaleur',           'description' => 'Systèmes de chauffage air/air et air/eau performants.'],
            ['name' => 'Chaudière',                 'description' => 'Chaudières gaz, fioul et à condensation pour le chauffage.'],
            ['name' => 'Isolation thermique',       'description' => 'Travaux d\'isolation des murs, toits et planchers.'],
            ['name' => 'Ballon thermodynamique',    'description' => 'Chauffe-eau thermodynamique pour eau chaude sanitaire.'],
            ['name' => 'Audit énergétique',         'description' => 'Diagnostic de performance énergétique et aides disponibles.'],
        ],
        'Télécom' => [
            ['name' => 'Forfait mobile',            'description' => 'Forfaits sans engagement, avec ou sans téléphone inclus.'],
            ['name' => 'Box internet',              'description' => 'Offres internet fibre et ADSL avec ou sans TV.'],
            ['name' => 'Fibre optique',             'description' => 'Très haut débit jusqu\'à 10 Gb/s pour les foyers fibrés.'],
            ['name' => 'ADSL',                      'description' => 'Internet haut débit pour les zones non encore fibrées.'],
            ['name' => 'Triple play',               'description' => 'Internet + téléphonie fixe + TV dans une seule offre.'],
            ['name' => 'Internet mobile 5G',        'description' => 'Clé 4G/5G et routeurs mobiles pour la mobilité.'],
            ['name' => 'Téléphonie d\'entreprise',  'description' => 'Solutions voix, standard virtuel et UCaaS pour pros.'],
            ['name' => 'Téléphone fixe',            'description' => 'Lignes fixes résidentielles et offres VoIP.'],
            ['name' => 'Roaming international',     'description' => 'Forfaits voyageurs avec data et appels à l\'étranger.'],
            ['name' => 'Offre satellite',           'description' => 'Internet par satellite pour les zones blanches.'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->sectorsByCategory as $categoryName => $sectors) {
            $categoryId = Category::query()
                ->where('slug', Str::slug($categoryName))
                ->value('id');

            if ($categoryId === null) {
                continue;
            }

            foreach ($sectors as $index => $data) {
                Sector::query()->updateOrCreate(
                    ['slug' => Str::slug($data['name'])],
                    [
                        'category_id' => $categoryId,
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'sort_order' => ($index + 1) * 10,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
