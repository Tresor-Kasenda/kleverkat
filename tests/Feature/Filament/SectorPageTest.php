<?php

use App\Filament\Pages\SectorPage;
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

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->callTableAction('create', data: [
            'name' => 'Assurances',
            'slug' => 'assurances',
            'description' => 'Tous les produits d assurance.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Sector::class, [
        'name' => 'Assurances',
        'slug' => 'assurances',
        'sort_order' => 1,
        'is_active' => true,
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

    $this->actingAs($admin);

    Livewire::test(SectorPage::class)
        ->callTableAction('edit', $sector, data: [
            'name' => 'Assurances Auto',
            'slug' => 'assurances-auto',
            'description' => 'Produits d assurance auto.',
            'sort_order' => 3,
            'is_active' => false,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Sector::class, [
        'id' => $sector->id,
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
