<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Example offers within each sector (the comparable products), keyed by the
     * sector slug. Inspired by the catalogues of comparison platforms.
     *
     * @var array<string, array<int, array{name: string, short_description: string}>>
     */
    private array $productsBySector = [
        'assurance-auto' => [
            ['name' => 'Auto au Tiers', 'short_description' => "Formule responsabilité civile, l'essentiel au meilleur prix."],
            ['name' => 'Auto Tiers Étendu', 'short_description' => 'Vol, incendie et bris de glace en plus du tiers.'],
            ['name' => 'Auto Tous Risques', 'short_description' => 'Protection complète du véhicule, y compris tous accidents.'],
        ],
        'assurance-moto-scooter' => [
            ['name' => 'Moto au Tiers', 'short_description' => 'Responsabilité civile pour deux-roues.'],
            ['name' => 'Moto Tous Risques', 'short_description' => 'Couverture complète moto et équipements.'],
        ],
        'assurance-habitation' => [
            ['name' => 'MRH Essentielle', 'short_description' => 'Couverture incendie, dégâts des eaux et vol.'],
            ['name' => 'MRH Confort', 'short_description' => 'Garanties étendues avec valeur à neuf et assistance.'],
        ],
        'assurance-sante-mutuelle' => [
            ['name' => 'Mutuelle Santé Solo', 'short_description' => 'Remboursement des soins courants pour une personne.'],
            ['name' => 'Mutuelle Santé Famille', 'short_description' => 'Prise en charge renforcée pour toute la famille.'],
        ],
        'assurance-emprunteur' => [
            ['name' => 'Assurance de Prêt Immobilier', 'short_description' => 'Garantie décès, invalidité et perte d\'emploi.'],
        ],
        'assurance-animaux' => [
            ['name' => 'Formule Chien & Chat', 'short_description' => 'Remboursement des frais vétérinaires et vaccins.'],
        ],
        'credit-immobilier' => [
            ['name' => 'Prêt Immobilier Taux Fixe', 'short_description' => 'Mensualités stables sur toute la durée du prêt.'],
            ['name' => 'Prêt Immobilier Taux Variable', 'short_description' => 'Taux révisable indexé sur le marché.'],
        ],
        'credit-a-la-consommation' => [
            ['name' => 'Prêt Personnel', 'short_description' => 'Financement libre sans justificatif d\'utilisation.'],
            ['name' => 'Crédit Renouvelable', 'short_description' => 'Réserve d\'argent disponible et reconstituable.'],
        ],
        'rachat-de-credit' => [
            ['name' => 'Rachat de Crédits Conso', 'short_description' => 'Regroupez vos crédits en une seule mensualité réduite.'],
        ],
        'electricite' => [
            ['name' => 'Électricité Verte', 'short_description' => "Électricité d'origine renouvelable à prix indexé."],
            ['name' => 'Électricité Prix Fixe', 'short_description' => 'Prix du kWh bloqué pendant 1 à 3 ans.'],
        ],
        'gaz' => [
            ['name' => 'Gaz Naturel Indexé', 'short_description' => 'Offre gaz indexée sur le prix repère.'],
        ],
        'box-internet' => [
            ['name' => 'Box Fibre', 'short_description' => 'Très haut débit fibre avec téléphonie et TV.'],
            ['name' => 'Box ADSL', 'short_description' => 'Internet ADSL pour les zones non fibrées.'],
        ],
        'forfait-mobile' => [
            ['name' => 'Forfait 20 Go', 'short_description' => 'Appels illimités et 20 Go de data sans engagement.'],
            ['name' => 'Forfait 100 Go', 'short_description' => 'Gros volume de data pour les usages intensifs.'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->productsBySector as $sectorSlug => $products) {
            $sector = Sector::query()->where('slug', $sectorSlug)->first();

            if ($sector === null) {
                continue;
            }

            foreach ($products as $index => $data) {
                Product::query()->firstOrCreate(
                    ['slug' => Str::slug($data['name'])],
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
