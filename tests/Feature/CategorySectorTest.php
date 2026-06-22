<?php

use App\Models\Category;
use App\Models\Sector;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SectorSeeder;

test('a sector belongs to a category', function () {
    $category = Category::factory()->create();
    $sector = Sector::factory()->for($category)->create();

    expect($sector->category)->not->toBeNull()
        ->and($sector->category->is($category))->toBeTrue();
});

test('a category has many sectors', function () {
    $category = Category::factory()->create();
    Sector::factory()->count(3)->for($category)->create();

    expect($category->sectors)->toHaveCount(3);
});

test('seeders attach every sector to a category', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(SectorSeeder::class);

    expect(Category::query()->count())->toBe(4)
        ->and(Sector::query()->count())->toBe(40)
        ->and(Sector::query()->whereNull('category_id')->count())->toBe(0);
});
