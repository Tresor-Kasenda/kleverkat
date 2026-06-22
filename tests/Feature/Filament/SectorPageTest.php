<?php

use App\Filament\Pages\SectorPage;
use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

test('admin can access sector page and see sectors', function () {
    $admin = User::factory()->admin()->create();
    $sectors = Sector::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(SectorPage::getUrl())->assertSuccessful();

    Livewire::test(SectorPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($sectors);
});

test('non admin can not access sector page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(SectorPage::getUrl())
        ->assertForbidden();
});

test('admin can create a sector from sector page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.category_id' => $category->id,
            'mountedActions.0.data.name' => 'Assurances',
            'mountedActions.0.data.slug' => 'assurances',
            'mountedActions.0.data.description' => 'Tous les produits d assurance.',
            'mountedActions.0.data.sort_order' => 1,
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Sector::class, [
        'category_id' => $category->id,
        'name' => 'Assurances',
        'slug' => 'assurances',
        'sort_order' => 1,
        'is_active' => true,
    ]);
});

test('creating a sector without a category is rejected', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.name' => 'Sans catégorie',
            'mountedActions.0.data.slug' => 'sans-categorie',
            'mountedActions.0.data.sort_order' => 1,
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasFormErrors(['category_id']);

    $this->assertDatabaseMissing(Sector::class, [
        'slug' => 'sans-categorie',
    ]);
});

test('admin can edit a sector from sector page', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create([
        'name' => 'Assurances',
        'slug' => 'assurances',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $newCategory = Category::factory()->create();

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->mountTableAction('edit', $sector)
        ->set([
            'mountedActions.0.data.category_id' => $newCategory->id,
            'mountedActions.0.data.name' => 'Assurances Auto',
            'mountedActions.0.data.slug' => 'assurances-auto',
            'mountedActions.0.data.description' => 'Produits d assurance auto.',
            'mountedActions.0.data.sort_order' => 3,
            'mountedActions.0.data.is_active' => false,
        ])
        ->callMountedTableAction()
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Sector::class, [
        'id' => $sector->id,
        'category_id' => $newCategory->id,
        'name' => 'Assurances Auto',
        'slug' => 'assurances-auto',
        'sort_order' => 3,
        'is_active' => false,
    ]);
});

test('admin can delete a sector from sector page', function () {
    $admin = User::factory()->admin()->create();
    $sector = Sector::factory()->create();

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->callTableAction('delete', $sector);

    $this->assertDatabaseMissing(Sector::class, [
        'id' => $sector->id,
    ]);
});
