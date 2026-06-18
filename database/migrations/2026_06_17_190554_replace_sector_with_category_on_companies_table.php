<?php

use App\Models\Category;
use App\Models\Sector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignIdFor(Category::class)
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignIdFor(Sector::class)
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
