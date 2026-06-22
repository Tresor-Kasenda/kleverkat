<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Sector;

test('the navigation menu shares only active catalogue entries in display order', function () {
    $category = Category::factory()->create([
        'name' => 'Assurance',
        'slug' => 'assurance',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    Category::factory()->create([
        'slug' => 'inactive-category',
        'is_active' => false,
    ]);

    $sector = Sector::factory()->for($category)->create([
        'name' => 'Assurance auto',
        'slug' => 'assurance-auto',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    Sector::factory()->for($category)->create([
        'slug' => 'inactive-sector',
        'is_active' => false,
    ]);

    $product = Product::factory()->for($sector)->create([
        'name' => 'Responsabilité civile',
        'slug' => 'responsabilite-civile',
        'sort_order' => 4,
        'is_active' => true,
    ]);

    Product::factory()->for($sector)->create([
        'slug' => 'inactive-product',
        'is_active' => false,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('navCategories', 1)
            ->where('navCategories.0.id', $category->id)
            ->where('navCategories.0.description', $category->description)
            ->has('navCategories.0.sectors', 1)
            ->where('navCategories.0.sectors.0.id', $sector->id)
            ->where('navCategories.0.sectors.0.description', $sector->description)
            ->has('navCategories.0.sectors.0.products', 1)
            ->where('navCategories.0.sectors.0.products.0.id', $product->id)
            ->where('navCategories.0.sectors.0.products.0.short_description', $product->short_description)
        );
});
