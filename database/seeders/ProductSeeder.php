<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * @var array<string, array<int, array{name: string, short_description: string}>>
     */
    private array $productsBySector = [
        'assurances' => [
            ['name' => 'Assurance Vie Classique', 'short_description' => 'Couverture décès avec capital garanti pour les bénéficiaires.'],
            ['name' => 'Assurance Santé Famille', 'short_description' => 'Prise en charge des frais médicaux pour toute la famille.'],
            ['name' => 'Assurance Auto Tous Risques', 'short_description' => 'Protection complète du véhicule contre tous les sinistres.'],
            ['name' => 'Assurance Habitation', 'short_description' => 'Couverture incendie, vol et dommages pour votre logement.'],
        ],
        'banque-finance' => [
            ['name' => 'Compte Épargne Plus', 'short_description' => 'Compte rémunéré à taux attractif sans frais de gestion.'],
            ['name' => 'Crédit Immobilier', 'short_description' => 'Financement de votre projet immobilier à taux compétitif.'],
            ['name' => 'Compte Courant Business', 'short_description' => 'Compte professionnel avec carte Visa et services en ligne.'],
        ],
        'telecommunications' => [
            ['name' => 'Forfait Mobile Pro', 'short_description' => 'Appels illimités, SMS et data haut débit pour professionnels.'],
            ['name' => 'Internet Fibre Entreprise', 'short_description' => 'Connexion fibre optique dédiée avec SLA garanti.'],
            ['name' => 'Solutions M-Banking', 'short_description' => 'Paiement mobile et transfert d\'argent via téléphone.'],
        ],
        'energie-mines' => [
            ['name' => 'Électricité Industrielle', 'short_description' => 'Fourniture d\'énergie électrique pour sites industriels.'],
            ['name' => 'Panneaux Solaires Résidentiels', 'short_description' => 'Installation et maintenance de systèmes solaires photovoltaïques.'],
        ],
        'sante' => [
            ['name' => 'Consultation Spécialisée', 'short_description' => 'Accès à des médecins spécialistes toutes disciplines.'],
            ['name' => 'Bilan de Santé Complet', 'short_description' => 'Bilan médical annuel avec analyses et imagerie incluses.'],
            ['name' => 'Pharmacie en Ligne', 'short_description' => 'Commande et livraison de médicaments avec ordonnance.'],
        ],
        'immobilier' => [
            ['name' => 'Location Bureaux Prestige', 'short_description' => 'Espaces de bureaux haut de gamme en centre-ville.'],
            ['name' => 'Vente Appartements Neufs', 'short_description' => 'Appartements en résidence sécurisée avec parking.'],
        ],
        'transport-logistique' => [
            ['name' => 'Transport de Marchandises', 'short_description' => 'Fret routier national avec suivi GPS en temps réel.'],
            ['name' => 'Logistique Entrepôt', 'short_description' => 'Stockage sécurisé et gestion de stocks pour entreprises.'],
            ['name' => 'Coursier Express', 'short_description' => 'Livraison express de documents et colis en ville.'],
        ],
        'commerce-distribution' => [
            ['name' => 'Distribution Alimentaire Gros', 'short_description' => 'Approvisionnement en produits alimentaires pour revendeurs.'],
            ['name' => 'Produits d\'Entretien B2B', 'short_description' => 'Fournitures de nettoyage et hygiène pour entreprises.'],
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
