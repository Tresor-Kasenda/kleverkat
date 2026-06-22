<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Offer;
use App\Models\OfferFeature;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanyOfferSeeder extends Seeder
{
    /**
     * Entreprises réelles par catégorie (slug).
     *
     * @var array<string, array<int, array<string, string>>>
     */
    private array $companiesByCategory = [
        'assurance' => [
            ['name' => 'AXA France',        'website' => 'https://axa.fr',         'email' => 'contact@axa.fr',         'phone' => '01 55 00 00 00', 'city' => 'Paris'],
            ['name' => 'Groupama',          'website' => 'https://groupama.fr',    'email' => 'contact@groupama.fr',    'phone' => '01 44 56 77 77', 'city' => 'Paris'],
            ['name' => 'MAIF',             'website' => 'https://maif.fr',        'email' => 'contact@maif.fr',        'phone' => '05 49 73 73 73', 'city' => 'Niort'],
            ['name' => 'Allianz France',    'website' => 'https://allianz.fr',     'email' => 'contact@allianz.fr',     'phone' => '01 58 85 88 88', 'city' => 'Paris'],
            ['name' => 'Generali France',   'website' => 'https://generali.fr',    'email' => 'contact@generali.fr',    'phone' => '01 58 38 11 00', 'city' => 'Paris'],
            ['name' => 'April Assurances',  'website' => 'https://april.fr',       'email' => 'contact@april.fr',       'phone' => '04 72 36 36 36', 'city' => 'Lyon'],
        ],
        'credit-banque' => [
            ['name' => 'BNP Paribas',       'website' => 'https://bnpparibas.fr',  'email' => 'contact@bnpparibas.fr',  'phone' => '01 40 14 45 46', 'city' => 'Paris'],
            ['name' => 'Crédit Agricole',   'website' => 'https://credit-agricole.fr', 'email' => 'info@credit-agricole.fr', 'phone' => '09 69 39 39 39', 'city' => 'Paris'],
            ['name' => 'Société Générale',  'website' => 'https://societegenerale.fr', 'email' => 'contact@sg.fr',      'phone' => '3933',           'city' => 'La Défense'],
            ['name' => 'LCL',              'website' => 'https://lcl.fr',         'email' => 'contact@lcl.fr',         'phone' => '3935',           'city' => 'Lyon'],
            ['name' => 'Boursorama Banque', 'website' => 'https://boursorama.com', 'email' => 'contact@boursorama.fr',  'phone' => '01 46 09 49 49', 'city' => 'Boulogne-Billancourt'],
            ['name' => 'Hello Bank',        'website' => 'https://hello.bank',     'email' => 'contact@hello-bank.fr',  'phone' => '01 40 55 40 55', 'city' => 'Paris'],
        ],
        'energie' => [
            ['name' => 'EDF',              'website' => 'https://edf.fr',         'email' => 'contact@edf.fr',         'phone' => '3004',           'city' => 'Paris'],
            ['name' => 'Engie',            'website' => 'https://engie.fr',       'email' => 'contact@engie.fr',       'phone' => '3088',           'city' => 'La Défense'],
            ['name' => 'TotalEnergies',    'website' => 'https://totalenergies.fr', 'email' => 'contact@totalenergies.fr', 'phone' => '09 69 39 39 39', 'city' => 'Courbevoie'],
            ['name' => 'Eni',              'website' => 'https://eni.com/fr',     'email' => 'contact@eni.fr',         'phone' => '0800 005 050',   'city' => 'Paris'],
            ['name' => 'Ekwateur',         'website' => 'https://ekwateur.fr',    'email' => 'contact@ekwateur.fr',    'phone' => '01 86 26 34 38', 'city' => 'Paris'],
            ['name' => 'Vattenfall',       'website' => 'https://vattenfall.fr',  'email' => 'contact@vattenfall.fr',  'phone' => '0800 005 009',   'city' => 'Paris'],
        ],
        'telecom' => [
            ['name' => 'Orange',           'website' => 'https://orange.fr',      'email' => 'contact@orange.fr',      'phone' => '3900',           'city' => 'Issy-les-Moulineaux'],
            ['name' => 'SFR',              'website' => 'https://sfr.fr',         'email' => 'contact@sfr.fr',         'phone' => '1023',           'city' => 'Saint-Denis'],
            ['name' => 'Bouygues Telecom', 'website' => 'https://bouyguestelecom.fr', 'email' => 'contact@bouyguestelecom.fr', 'phone' => '1064', 'city' => 'Issy-les-Moulineaux'],
            ['name' => 'Free',             'website' => 'https://free.fr',        'email' => 'contact@free.fr',        'phone' => '3244',           'city' => 'Paris'],
            ['name' => 'Prixtel',          'website' => 'https://prixtel.com',    'email' => 'contact@prixtel.com',    'phone' => '09 75 18 50 00', 'city' => 'Toulouse'],
            ['name' => 'Syma Mobile',      'website' => 'https://symamobile.com', 'email' => 'contact@symamobile.com', 'phone' => '0800 000 900',   'city' => 'Paris'],
        ],
    ];

    /**
     * Prix de base par secteur (slug) — en €/mois sauf précision.
     *
     * @var array<string, float>
     */
    private array $basePriceBySector = [
        'assurance-auto' => 65.00,
        'assurance-moto' => 42.00,
        'assurance-habitation' => 22.00,
        'mutuelle-sante' => 55.00,
        'assurance-emprunteur' => 38.00,
        'assurance-animaux' => 28.00,
        'assurance-voyage' => 45.00,
        'assurance-scolaire' => 12.00,
        'assurance-vie' => 0.00,
        'prevoyance-individuelle' => 35.00,
        'credit-immobilier' => 950.00,
        'credit-a-la-consommation' => 180.00,
        'rachat-de-credits' => 620.00,
        'epargne-placements' => 0.00,
        'compte-bancaire' => 7.00,
        'carte-bancaire' => 12.00,
        'credit-auto' => 240.00,
        'pret-etudiant' => 150.00,
        'bourse-investissement' => 5.00,
        'assurance-emprunteur-banque' => 52.00,
        'electricite' => 110.00,
        'gaz-naturel' => 85.00,
        'electricite-gaz' => 175.00,
        'energie-verte' => 118.00,
        'panneaux-solaires' => 0.00,
        'pompe-a-chaleur' => 0.00,
        'chaudiere' => 0.00,
        'isolation-thermique' => 0.00,
        'ballon-thermodynamique' => 0.00,
        'audit-energetique' => 350.00,
        'forfait-mobile' => 12.00,
        'box-internet' => 29.00,
        'fibre-optique' => 35.00,
        'adsl' => 22.00,
        'triple-play' => 40.00,
        'internet-mobile-5g' => 25.00,
        'telephonie-dentreprise' => 18.00,
        'telephone-fixe' => 6.00,
        'roaming-international' => 20.00,
        'offre-satellite' => 65.00,
    ];

    /**
     * Multiplicateurs de prix par rang de compagnie (6 compagnies).
     *
     * @var array<int, float>
     */
    private array $priceMultipliers = [1.00, 1.12, 0.92, 1.05, 0.88, 1.18];

    /**
     * Features génériques par catégorie.
     *
     * @var array<string, array<int, array<int, array{label: string, value: string, highlight: bool}>>>
     */
    private array $featuresTemplates = [
        'assurance' => [
            [
                ['label' => 'Franchise',          'value' => '0 €',        'highlight' => true],
                ['label' => 'Assistance',          'value' => '24h/24',     'highlight' => false],
                ['label' => 'Délai de carence',    'value' => 'Aucun',      'highlight' => false],
                ['label' => 'Gestion en ligne',    'value' => '100%',       'highlight' => false],
            ],
            [
                ['label' => 'Franchise',          'value' => '150 €',      'highlight' => false],
                ['label' => 'Assistance',          'value' => '0 km',       'highlight' => true],
                ['label' => 'Protection juridique', 'value' => 'Incluse',   'highlight' => false],
                ['label' => 'Gestion sinistres',   'value' => '< 72h',      'highlight' => false],
            ],
            [
                ['label' => 'Franchise',          'value' => '300 €',      'highlight' => false],
                ['label' => 'Résiliation',         'value' => 'Sans frais', 'highlight' => true],
                ['label' => 'Prise d\'effet',      'value' => 'Immédiate',  'highlight' => false],
                ['label' => 'Remboursement',       'value' => 'En 5 jours', 'highlight' => false],
            ],
        ],
        'credit-banque' => [
            [
                ['label' => 'TAEG',              'value' => '3,5%',         'highlight' => true],
                ['label' => 'Sans frais de dossier', 'value' => 'Oui',     'highlight' => false],
                ['label' => 'Remboursement anticipé', 'value' => 'Gratuit', 'highlight' => false],
                ['label' => 'Délai de réponse',  'value' => '24h',          'highlight' => false],
            ],
            [
                ['label' => 'TAEG',              'value' => '2,9%',         'highlight' => true],
                ['label' => 'Frais de dossier',  'value' => '150 €',        'highlight' => false],
                ['label' => 'Assurance incluse', 'value' => 'Oui',          'highlight' => false],
                ['label' => 'Délai de réponse',  'value' => '48h',          'highlight' => false],
            ],
            [
                ['label' => 'TAEG',              'value' => '4,2%',         'highlight' => false],
                ['label' => 'Frais de dossier',  'value' => 'Offerts',      'highlight' => true],
                ['label' => 'Modularité',         'value' => 'Incluse',      'highlight' => false],
                ['label' => 'Délai de réponse',  'value' => 'Immédiat',     'highlight' => false],
            ],
        ],
        'energie' => [
            [
                ['label' => 'Engagement',        'value' => 'Sans',         'highlight' => true],
                ['label' => 'Prix kWh',          'value' => '0,1950 €',     'highlight' => false],
                ['label' => 'Économie estimée',   'value' => '120 €/an',     'highlight' => false],
                ['label' => 'Activation',         'value' => '< 10 jours',   'highlight' => false],
            ],
            [
                ['label' => 'Engagement',        'value' => '12 mois',      'highlight' => false],
                ['label' => 'Prix kWh',          'value' => '0,1820 €',     'highlight' => true],
                ['label' => 'Énergie verte',      'value' => '100%',         'highlight' => false],
                ['label' => 'Activation',         'value' => '< 7 jours',    'highlight' => false],
            ],
            [
                ['label' => 'Engagement',        'value' => '24 mois',      'highlight' => false],
                ['label' => 'Prix kWh',          'value' => '0,1760 €',     'highlight' => true],
                ['label' => 'Primes fidélité',    'value' => 'Oui',          'highlight' => false],
                ['label' => 'Activation',         'value' => '< 14 jours',   'highlight' => false],
            ],
        ],
        'telecom' => [
            [
                ['label' => 'Réseau',            'value' => '4G / 5G',      'highlight' => false],
                ['label' => 'Engagement',         'value' => 'Sans',         'highlight' => true],
                ['label' => 'Roaming UE',         'value' => 'Inclus',       'highlight' => false],
                ['label' => 'Activation',         'value' => 'Gratuite',     'highlight' => false],
            ],
            [
                ['label' => 'Réseau',            'value' => '5G',            'highlight' => true],
                ['label' => 'Engagement',         'value' => '12 mois',      'highlight' => false],
                ['label' => 'Appels illimités',   'value' => 'Oui',          'highlight' => false],
                ['label' => 'Activation',         'value' => '9,90 €',       'highlight' => false],
            ],
            [
                ['label' => 'Réseau',            'value' => '4G+',           'highlight' => false],
                ['label' => 'Engagement',         'value' => '24 mois',      'highlight' => false],
                ['label' => 'SMS/MMS illimités',  'value' => 'Oui',          'highlight' => false],
                ['label' => 'Activation',         'value' => 'Offerte',      'highlight' => true],
            ],
        ],
    ];

    public function run(): void
    {
        $categories = Category::query()->where('is_active', true)->with('sectors.products')->get();

        foreach ($categories as $category) {
            $companyDefs = $this->companiesByCategory[$category->slug] ?? [];

            if (empty($companyDefs)) {
                continue;
            }

            $companies = [];
            foreach ($companyDefs as $idx => $def) {
                $slug = Str::slug($def['name']);

                $company = Company::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'name' => $def['name'],
                        'description' => 'Comparez les offres '.$def['name'].' sur '.config('app.name').'.',
                        'website_url' => $def['website'],
                        'support_email' => $def['email'],
                        'support_phone' => $def['phone'],
                        'city' => $def['city'],
                        'country' => 'France',
                        'is_active' => true,
                    ],
                );

                $companies[$idx] = $company;
            }

            $products = Product::query()
                ->whereHas('sector', fn ($q) => $q->where('category_id', $category->id)->where('is_active', true))
                ->where('is_active', true)
                ->with('sector')
                ->get();

            $featuresTemplate = $this->featuresTemplates[$category->slug] ?? [];
            $numCompanies = count($companies);

            foreach ($products as $productIndex => $product) {
                $sectorSlug = $product->sector?->slug ?? '';
                $basePrice = $this->basePriceBySector[$sectorSlug] ?? 49.00;

                $selectedCompanyIndexes = [
                    $productIndex % $numCompanies,
                    ($productIndex + 1) % $numCompanies,
                    ($productIndex + 2) % $numCompanies,
                ];

                foreach ($selectedCompanyIndexes as $rank => $companyIndex) {
                    $company = $companies[$companyIndex];
                    $multiplier = $this->priceMultipliers[$companyIndex];
                    $price = $basePrice > 0 ? round($basePrice * $multiplier, 2) : 0.00;
                    $priceNote = $basePrice === 0.0 ? 'Tarif sur devis selon votre projet' : null;

                    $offerSlug = Str::slug($company->name.'-'.$product->slug.'-'.$rank);

                    $offer = Offer::query()->updateOrCreate(
                        ['slug' => $offerSlug],
                        [
                            'company_id' => $company->id,
                            'product_id' => $product->id,
                            'name' => $product->name.' – '.$company->name,
                            'short_description' => $product->short_description,
                            'long_description' => $product->short_description,
                            'base_price' => $price,
                            'price_note' => $priceNote,
                            'sort_order' => ($rank + 1) * 10,
                            'is_active' => true,
                            'is_featured' => $rank === 0 && ($productIndex % 5 === 0),
                        ],
                    );

                    $offer->features()->delete();

                    $featureSet = $featuresTemplate[$companyIndex % count($featuresTemplate)] ?? [];

                    foreach ($featureSet as $featureOrder => $feat) {
                        OfferFeature::query()->create([
                            'offer_id' => $offer->id,
                            'label' => $feat['label'],
                            'value' => $feat['value'],
                            'is_highlight' => $feat['highlight'],
                            'sort_order' => ($featureOrder + 1) * 10,
                        ]);
                    }
                }
            }
        }
    }
}
