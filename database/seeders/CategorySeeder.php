<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Assurance',      'slug' => 'assurance',     'description' => 'Comparez toutes vos assurances : auto, moto, habitation, santé, vie et bien plus.', 'sort_order' => 1],
            ['name' => 'Crédit & Banque', 'slug' => 'credit-banque', 'description' => 'Trouvez le meilleur crédit, compte bancaire, carte et placement pour votre situation.', 'sort_order' => 2],
            ['name' => 'Énergie',        'slug' => 'energie',       'description' => 'Comparez les offres d\'électricité, de gaz et d\'énergies renouvelables.', 'sort_order' => 3],
            ['name' => 'Télécom',        'slug' => 'telecom',       'description' => 'Forfait mobile, box internet, fibre optique : comparez les meilleures offres télécom.', 'sort_order' => 4],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true]),
            );
        }
    }
}
