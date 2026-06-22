<?php

use App\Filament\Pages\CategoryPage;
use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

test('admin can access category page and see categories', function () {
    $admin = User::factory()->admin()->create();
    $categories = Category::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(CategoryPage::getUrl())->assertSuccessful();

    Livewire::test(CategoryPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($categories);
});

test('non admin can not access category page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(CategoryPage::getUrl())
        ->assertForbidden();
});

test('admin can create a category from category page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(CategoryPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.name' => 'Services financiers',
            'mountedActions.0.data.slug' => 'services-financiers',
            'mountedActions.0.data.description' => 'Banque, assurance et investissement.',
            'mountedActions.0.data.sort_order' => 1,
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => 'Services financiers',
        'slug' => 'services-financiers',
        'sort_order' => 1,
        'is_active' => true,
    ]);
});

test('admin can edit a category from category page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CategoryPage::class)
        ->mountTableAction('edit', $category)
        ->set([
            'mountedActions.0.data.name' => 'Énergie & Industrie',
            'mountedActions.0.data.slug' => 'energie-industrie',
            'mountedActions.0.data.description' => 'Énergie, mines et transport.',
            'mountedActions.0.data.sort_order' => 5,
            'mountedActions.0.data.is_active' => false,
        ])
        ->callMountedTableAction()
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'id' => $category->id,
        'name' => 'Énergie & Industrie',
        'slug' => 'energie-industrie',
        'sort_order' => 5,
        'is_active' => false,
    ]);
});

test('admin can delete a category from category page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CategoryPage::class)
        ->callTableAction('delete', $category);

    $this->assertDatabaseMissing(Category::class, [
        'id' => $category->id,
    ]);
});

test('deleting a category detaches its sectors instead of deleting them', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $sector = Sector::factory()->for($category)->create();

    $this->actingAs($admin);

    Livewire::test(CategoryPage::class)
        ->callTableAction('delete', $category);

    expect($sector->fresh())->not->toBeNull()
        ->and($sector->fresh()->category_id)->toBeNull();
});
