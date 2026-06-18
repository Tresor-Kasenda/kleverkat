<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectorSeeder extends Seeder
{
    /**
     * Sectors are the comparison "verticals" within each universe, modelled on
     * lesfurets (assurance auto, assurance santé, crédit conso, électricité…).
     *
     * @var array<int, array{name: string, category: string, description: string}>
     */
    private array $sectors = [
        // --- Assurance ---
        [
            'name' => 'Assurance auto',
            'category' => 'Assurance',
            'description' => 'Comparez les formules au tiers, intermédiaires et tous risques.',
        ],
        [
            'name' => 'Assurance moto & scooter',
            'category' => 'Assurance',
            'description' => 'Couverture deux-roues pour motos et scooters.',
        ],
        [
            'name' => 'Assurance habitation',
            'category' => 'Assurance',
            'description' => 'Multirisque habitation (MRH) pour locataires et propriétaires.',
        ],
        [
            'name' => 'Assurance santé (mutuelle)',
            'category' => 'Assurance',
            'description' => 'Complémentaires santé individuelles et familiales.',
        ],
        [
            'name' => 'Assurance emprunteur',
            'category' => 'Assurance',
            'description' => 'Assurance de prêt pour sécuriser un crédit immobilier.',
        ],
        [
            'name' => 'Assurance animaux',
            'category' => 'Assurance',
            'description' => 'Frais vétérinaires pour chiens et chats.',
        ],

        // --- Crédit & Banque ---
        [
            'name' => 'Crédit immobilier',
            'category' => 'Crédit & Banque',
            'description' => 'Financement de l\'achat d\'un bien immobilier.',
        ],
        [
            'name' => 'Crédit à la consommation',
            'category' => 'Crédit & Banque',
            'description' => 'Prêt personnel et crédit renouvelable.',
        ],
        [
            'name' => 'Rachat de crédit',
            'category' => 'Crédit & Banque',
            'description' => 'Regroupement de crédits pour alléger les mensualités.',
        ],

        // --- Énergie ---
        [
            'name' => 'Électricité',
            'category' => 'Énergie',
            'description' => 'Offres de fourniture d\'électricité.',
        ],
        [
            'name' => 'Gaz',
            'category' => 'Énergie',
            'description' => 'Offres de fourniture de gaz naturel.',
        ],

        // --- Télécom ---
        [
            'name' => 'Box internet',
            'category' => 'Télécom',
            'description' => 'Offres internet fibre et ADSL.',
        ],
        [
            'name' => 'Forfait mobile',
            'category' => 'Télécom',
            'description' => 'Forfaits mobiles avec ou sans engagement.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->sectors as $index => $data) {
            $categoryId = Category::query()
                ->where('slug', Str::slug($data['category']))
                ->value('id');

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
