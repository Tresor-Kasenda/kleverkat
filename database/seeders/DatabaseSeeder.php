<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::query()->where('email', 'admin@example.com')->doesntExist()) {
            User::factory()->admin()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        $this->call([
            CategorySeeder::class,
            SectorSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
