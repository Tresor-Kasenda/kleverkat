<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Identification
            $table->string('code')->nullable()->unique()->after('sector_id');
            $table->string('category')->nullable()->after('slug');

            // Tarification
            $table->string('price_type')->default('on_quote')->after('description');
            $table->decimal('base_price', 12, 2)->nullable()->after('price_type');
            $table->char('currency', 3)->default('USD')->after('base_price');
            $table->string('billing_frequency')->nullable()->after('currency');

            // Conditions d'éligibilité
            $table->unsignedTinyInteger('min_age')->nullable()->after('billing_frequency');
            $table->unsignedTinyInteger('max_age')->nullable()->after('min_age');
            $table->decimal('min_insured_amount', 15, 2)->nullable()->after('max_age');
            $table->decimal('max_insured_amount', 15, 2)->nullable()->after('min_insured_amount');
            $table->unsignedSmallInteger('duration_months')->nullable()->after('max_insured_amount');
            $table->unsignedSmallInteger('waiting_period_days')->nullable()->after('duration_months');

            // Garanties & exclusions
            $table->json('features')->nullable()->after('waiting_period_days');
            $table->json('exclusions')->nullable()->after('features');

            // Publication
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->date('available_from')->nullable()->after('is_featured');
            $table->date('available_until')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'category',
                'price_type', 'base_price', 'currency', 'billing_frequency',
                'min_age', 'max_age', 'min_insured_amount', 'max_insured_amount',
                'duration_months', 'waiting_period_days',
                'features', 'exclusions',
                'is_featured', 'available_from', 'available_until',
            ]);
        });
    }
};
