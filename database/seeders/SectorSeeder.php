<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectorSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, description: string}>
     */
    private array $sectors = [
        [
            'name' => 'Assurances',
            'description' => 'Produits d\'assurance vie, santé, automobile et habitation.',
        ],
        [
            'name' => 'Banque & Finance',
            'description' => 'Services bancaires, crédit, épargne et investissement.',
        ],
        [
            'name' => 'Télécommunications',
            'description' => 'Opérateurs mobiles, internet et services de téléphonie.',
        ],
        [
            'name' => 'Énergie & Mines',
            'description' => 'Production et distribution d\'énergie, exploitation minière.',
        ],
        [
            'name' => 'Santé',
            'description' => 'Cliniques, hôpitaux, pharmacies et services médicaux.',
        ],
        [
            'name' => 'Immobilier',
            'description' => 'Promotion immobilière, agences et gestion de patrimoine.',
        ],
        [
            'name' => 'Transport & Logistique',
            'description' => 'Fret, transport de passagers et gestion de la chaîne d\'approvisionnement.',
        ],
        [
            'name' => 'Commerce & Distribution',
            'description' => 'Commerce de gros, détail et distribution de biens de consommation.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->sectors as $index => $data) {
            Sector::query()->firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
