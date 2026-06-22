<?php

use App\Filament\Pages\ProductsPage;
use App\Models\Category;
use App\Models\Sector;
use App\Models\User;
use Livewire\Livewire;

test('admin can select category and then filtered sectors in product form', function () {
    $admin = User::factory()->admin()->create();

    $categoryA = Category::factory()->create(['name' => 'Category A']);
    $categoryB = Category::factory()->create(['name' => 'Category B']);

    $sectorA1 = Sector::factory()->create(['name' => 'Sector A1', 'category_id' => $categoryA->id]);
    $sectorA2 = Sector::factory()->create(['name' => 'Sector A2', 'category_id' => $categoryA->id]);
    $sectorB1 = Sector::factory()->create(['name' => 'Sector B1', 'category_id' => $categoryB->id]);

    $this->actingAs($admin);

    Livewire::test(ProductsPage::class)
        ->mountAction('create')
        ->assertSchemaStateSet([
            'category_id' => null,
            'sector_id' => null,
        ])
        ->set([
            'mountedActions.0.data.category_id' => $categoryA->id,
        ])
        ->assertSchemaStateSet([
            'category_id' => $categoryA->id,
            'sector_id' => null,
        ])
        ->set([
            'mountedActions.0.data.category_id' => $categoryA->id,
            'mountedActions.0.data.sector_id' => $sectorA1->id,
        ])
        ->assertSchemaStateSet([
            'category_id' => $categoryA->id,
            'sector_id' => $sectorA1->id,
        ])
        ->assertHasNoActionErrors();
});
