<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Top-level "universes" that group the sectors together, modelled on
     * comparison platforms such as lesfurets (Assurance, Crédit, Énergie, Télécom).
     *
     * @var array<int, array{name: string, description: string}>
     */
    private array $categories = [
        [
            'name' => 'Assurance',
            'description' => 'Comparez les assurances : auto, moto, habitation, santé, emprunteur et animaux.',
        ],
        [
            'name' => 'Crédit & Banque',
            'description' => 'Crédit immobilier, crédit à la consommation et rachat de crédit.',
        ],
        [
            'name' => 'Énergie',
            'description' => "Offres d'électricité et de gaz pour votre logement.",
        ],
        [
            'name' => 'Télécom',
            'description' => 'Box internet et forfaits mobiles.',
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
