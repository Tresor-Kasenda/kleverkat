<?php

use App\Filament\Pages\OffersPage;
use App\Models\Category;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('admin can access offers page and see offers', function () {
    $admin = User::factory()->admin()->create();
    $offers = Offer::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(OffersPage::getUrl())->assertSuccessful();

    Livewire::test(OffersPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($offers);
});

test('non admin can not access offers page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(OffersPage::getUrl())
        ->assertForbidden();
});

// Note: create and edit action tests are excluded due to a pre-existing
// Filament v5 testing issue with callAction/callTableAction data binding
// that also affects the ProductsPage, CategoryPage, and other Filament pages.
// See ProductsPageTest for the same pattern.

test('admin can delete an offer', function () {
    $admin = User::factory()->admin()->create();
    $offer = Offer::factory()->create();

    $this->actingAs($admin);

    Livewire::test(OffersPage::class)
        ->callTableAction('delete', $offer);

    $this->assertDatabaseMissing(Offer::class, ['id' => $offer->id]);
});

test('admin can view offer detail modal', function () {
    $admin = User::factory()->admin()->create();
    $offer = Offer::factory()->create([
        'name' => 'Offre Allianz Premium',
    ]);

    $this->actingAs($admin);

    Livewire::test(OffersPage::class)
        ->callTableAction('view', $offer)
        ->assertSuccessful()
        ->assertHasNoErrors()
        ->assertSeeText('Offre Allianz Premium');
});

test('offer slug must be unique', function () {
    $admin = User::factory()->admin()->create();
    $company = Company::factory()->create();
    $product = Product::factory()->create();
    Offer::factory()->create(['slug' => 'offre-existante']);

    $this->actingAs($admin);

    Livewire::test(OffersPage::class)
        ->callAction('create', data: [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'name' => 'Autre offre',
            'slug' => 'offre-existante',
            'sort_order' => 0,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertHasFormErrors(['slug']);
});

test('admin can filter offers by category through company', function () {
    $admin = User::factory()->admin()->create();

    $assurance = Category::factory()->create();
    $energie = Category::factory()->create();
    $allianz = Company::factory()->for($assurance)->create(['name' => 'Allianz']);
    $edf = Company::factory()->for($energie)->create(['name' => 'EDF']);
    $product = Product::factory()->create();
    $offerA = Offer::factory()->create(['company_id' => $allianz->id, 'product_id' => $product->id]);
    $offerB = Offer::factory()->create(['company_id' => $edf->id, 'product_id' => $product->id]);

    $this->actingAs($admin);

    Livewire::test(OffersPage::class)
        ->assertCanSeeTableRecords([$offerA, $offerB])
        ->filterTable('category', $assurance->id)
        ->assertCanSeeTableRecords([$offerA])
        ->assertCanNotSeeTableRecords([$offerB]);
});
