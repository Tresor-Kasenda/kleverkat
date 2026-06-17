<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * High-level categories that group the sectors together. Inspired by standard
     * industry-classification hierarchies (e.g. GICS sectors → industry groups),
     * adapted to the Kleverkat catalogue.
     *
     * @var array<int, array{name: string, description: string}>
     */
    private array $categories = [
        [
            'name' => 'Services financiers',
            'description' => 'Assurances, banque, crédit, épargne et investissement.',
        ],
        [
            'name' => 'Énergie & Industrie',
            'description' => 'Énergie, mines, industrie, transport et logistique.',
        ],
        [
            'name' => 'Technologies & Télécommunications',
            'description' => 'Opérateurs télécoms, internet et services numériques.',
        ],
        [
            'name' => 'Santé & Bien-être',
            'description' => 'Cliniques, hôpitaux, pharmacies et services médicaux.',
        ],
        [
            'name' => 'Immobilier & Construction',
            'description' => 'Promotion immobilière, BTP et gestion de patrimoine.',
        ],
        [
            'name' => 'Commerce & Consommation',
            'description' => 'Commerce de gros, de détail et biens de consommation.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->categories as $index => $data) {
            Category::query()->firstOrCreate(
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
