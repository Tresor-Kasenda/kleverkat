<?php

use App\Enums\ProductBillingFrequency;
use App\Enums\ProductCategory;
use App\Enums\ProductPriceType;
use App\Filament\Pages\ProductsPage;
use App\Models\Product;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

test('admin can access products page and see products', function () {
    $admin = User::factory()->admin()->create();
    $products = Product::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(ProductsPage::getUrl())->assertSuccessful();

    Livewire::test(ProductsPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($products);
});

test('non admin can not access products page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(ProductsPage::getUrl())
        ->assertForbidden();
});

test('admin can create a product with all fields', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create(['name' => 'Assurances', 'slug' => 'assurances']);

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('create', data: [
            'sector_id' => $sector->id,
            'code' => 'ASS-VIE-001',
            'name' => 'Assurance Vie Classique',
            'slug' => 'assurance-vie-classique',
            'category' => ProductCategory::Vie->value,
            'short_description' => 'Couverture décès avec capital garanti.',
            'description' => 'Produit complet pour assurer votre famille.',
            'price_type' => ProductPriceType::Fixed->value,
            'base_price' => 25.00,
            'currency' => 'USD',
            'billing_frequency' => ProductBillingFrequency::Monthly->value,
            'min_age' => 18,
            'max_age' => 65,
            'min_insured_amount' => 1000.00,
            'max_insured_amount' => 500000.00,
            'duration_months' => 12,
            'waiting_period_days' => 30,
            'features' => [['label' => 'Capital décès garanti']],
            'exclusions' => [['label' => 'Suicide dans les 12 premiers mois']],
            'sort_order' => 10,
            'is_active' => true,
            'is_featured' => true,
            'available_from' => '2026-01-01',
            'available_until' => null,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'sector_id' => $sector->id,
        'code' => 'ASS-VIE-001',
        'name' => 'Assurance Vie Classique',
        'slug' => 'assurance-vie-classique',
        'price_type' => ProductPriceType::Fixed->value,
        'billing_frequency' => ProductBillingFrequency::Monthly->value,
        'min_age' => 18,
        'max_age' => 65,
        'duration_months' => 12,
        'waiting_period_days' => 30,
        'is_active' => true,
        'is_featured' => true,
    ]);
});

test('admin can create a product with minimal fields', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('create', data: [
            'sector_id' => $sector->id,
            'name' => 'Produit sur devis',
            'slug' => 'produit-sur-devis',
            'price_type' => ProductPriceType::OnQuote->value,
            'currency' => 'USD',
            'sort_order' => 0,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => 'Produit sur devis',
        'price_type' => ProductPriceType::OnQuote->value,
        'base_price' => null,
        'min_age' => null,
        'max_age' => null,
    ]);
});

test('admin can edit a product', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create();
    $product = Product::factory()->create([
        'sector_id' => $sector->id,
        'name' => 'Produit Initial',
        'slug' => 'produit-initial',
        'price_type' => ProductPriceType::OnQuote->value,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('edit', $product, data: [
            'sector_id' => $sector->id,
            'name' => 'Produit Mis À Jour',
            'slug' => 'produit-mis-a-jour',
            'price_type' => ProductPriceType::Fixed->value,
            'base_price' => 50.00,
            'currency' => 'CDF',
            'billing_frequency' => ProductBillingFrequency::Annual->value,
            'min_age' => 21,
            'max_age' => 60,
            'duration_months' => 24,
            'is_active' => false,
            'is_featured' => true,
            'sort_order' => 5,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'id' => $product->id,
        'name' => 'Produit Mis À Jour',
        'price_type' => ProductPriceType::Fixed->value,
        'currency' => 'CDF',
        'billing_frequency' => ProductBillingFrequency::Annual->value,
        'min_age' => 21,
        'max_age' => 60,
        'duration_months' => 24,
        'is_active' => false,
        'is_featured' => true,
    ]);
});

test('admin can delete a product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('delete', $product);

    $this->assertDatabaseMissing(Product::class, ['id' => $product->id]);
});

test('admin can view product detail modal', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'name' => 'Assurance Vie Premium',
        'code' => 'ASS-VIE-001',
        'category' => ProductCategory::Vie->value,
        'price_type' => ProductPriceType::Fixed->value,
        'base_price' => 25.00,
        'currency' => 'USD',
        'billing_frequency' => ProductBillingFrequency::Monthly->value,
        'min_age' => 18,
        'max_age' => 65,
        'duration_months' => 12,
        'waiting_period_days' => 30,
        'features' => [['label' => 'Capital décès garanti']],
        'exclusions' => [['label' => 'Suicide dans les 12 premiers mois']],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('view', $product)
        ->assertSuccessful()
        ->assertHasNoErrors()
        ->assertSeeText('Assurance Vie Premium');
});

test('product code must be unique', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create();
    Product::factory()->create(['code' => 'ASS-001']);

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->callTableAction('create', data: [
            'sector_id' => $sector->id,
            'name' => 'Autre produit',
            'slug' => 'autre-produit',
            'code' => 'ASS-001',
            'price_type' => ProductPriceType::OnQuote->value,
            'currency' => 'USD',
            'sort_order' => 0,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertHasFormErrors(['code']);
});
