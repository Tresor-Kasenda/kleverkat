<?php

use App\Enums\ProductBillingFrequency;
use App\Enums\ProductPriceType;
use App\Filament\Pages\ProductsPage;
use App\Models\Category;
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
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.category_id' => $sector->category_id,
            'mountedActions.0.data.sector_id' => $sector->id,
            'mountedActions.0.data.code' => 'ASS-VIE-001',
            'mountedActions.0.data.name' => 'Assurance Vie Classique',
            'mountedActions.0.data.slug' => 'assurance-vie-classique',
            'mountedActions.0.data.short_description' => 'Couverture décès avec capital garanti.',
            'mountedActions.0.data.description' => 'Produit complet pour assurer votre famille.',
            'mountedActions.0.data.price_type' => ProductPriceType::Fixed->value,
            'mountedActions.0.data.base_price' => 25.00,
            'mountedActions.0.data.currency' => 'USD',
            'mountedActions.0.data.billing_frequency' => ProductBillingFrequency::Monthly->value,
            'mountedActions.0.data.min_age' => 18,
            'mountedActions.0.data.max_age' => 65,
            'mountedActions.0.data.min_insured_amount' => 1000.00,
            'mountedActions.0.data.max_insured_amount' => 500000.00,
            'mountedActions.0.data.duration_months' => 12,
            'mountedActions.0.data.waiting_period_days' => 30,
            'mountedActions.0.data.features' => [['label' => 'Capital décès garanti']],
            'mountedActions.0.data.exclusions' => [['label' => 'Suicide dans les 12 premiers mois']],
            'mountedActions.0.data.sort_order' => 10,
            'mountedActions.0.data.is_active' => true,
            'mountedActions.0.data.is_featured' => true,
            'mountedActions.0.data.available_from' => '2026-01-01',
            'mountedActions.0.data.available_until' => null,
        ])
        ->call('callMountedAction')
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
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.category_id' => $sector->category_id,
            'mountedActions.0.data.sector_id' => $sector->id,
            'mountedActions.0.data.name' => 'Produit sur devis',
            'mountedActions.0.data.slug' => 'produit-sur-devis',
            'mountedActions.0.data.price_type' => ProductPriceType::OnQuote->value,
            'mountedActions.0.data.currency' => 'USD',
            'mountedActions.0.data.sort_order' => 0,
            'mountedActions.0.data.is_active' => true,
            'mountedActions.0.data.is_featured' => false,
        ])
        ->call('callMountedAction')
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
        ->mountTableAction('edit', $product)
        ->set([
            'mountedActions.0.data.category_id' => $sector->category_id,
            'mountedActions.0.data.sector_id' => $sector->id,
            'mountedActions.0.data.name' => 'Produit Mis À Jour',
            'mountedActions.0.data.slug' => 'produit-mis-a-jour',
            'mountedActions.0.data.price_type' => ProductPriceType::Fixed->value,
            'mountedActions.0.data.base_price' => 50.00,
            'mountedActions.0.data.currency' => 'CDF',
            'mountedActions.0.data.billing_frequency' => ProductBillingFrequency::Annual->value,
            'mountedActions.0.data.min_age' => 21,
            'mountedActions.0.data.max_age' => 60,
            'mountedActions.0.data.duration_months' => 24,
            'mountedActions.0.data.is_active' => false,
            'mountedActions.0.data.is_featured' => true,
            'mountedActions.0.data.sort_order' => 5,
        ])
        ->callMountedTableAction()
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
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.category_id' => $sector->category_id,
            'mountedActions.0.data.sector_id' => $sector->id,
            'mountedActions.0.data.name' => 'Autre produit',
            'mountedActions.0.data.slug' => 'autre-produit',
            'mountedActions.0.data.code' => 'ASS-001',
            'mountedActions.0.data.price_type' => ProductPriceType::OnQuote->value,
            'mountedActions.0.data.currency' => 'USD',
            'mountedActions.0.data.sort_order' => 0,
            'mountedActions.0.data.is_active' => true,
            'mountedActions.0.data.is_featured' => false,
        ])
        ->call('callMountedAction')
        ->assertHasFormErrors(['code']);
});

test('admin can filter products by category through their sector', function () {
    $admin = User::factory()->admin()->create();

    $assurance = Category::factory()->create();
    $energie = Category::factory()->create();
    $autoProduct = Product::factory()->for(Sector::factory()->for($assurance))->create();
    $elecProduct = Product::factory()->for(Sector::factory()->for($energie))->create();

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->assertCanSeeTableRecords([$autoProduct, $elecProduct])
        ->filterTable('category', $assurance->id)
        ->assertCanSeeTableRecords([$autoProduct])
        ->assertCanNotSeeTableRecords([$elecProduct]);
});
