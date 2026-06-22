<?php

use App\Models\Offer;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Offer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->string('rule_type');
            $table->string('operator');
            $table->string('expected_value');
            $table->decimal('weight', 5, 3)->nullable();
            $table->decimal('score_delta', 10, 2)->nullable();
            $table->decimal('price_delta', 12, 2)->nullable();
            $table->decimal('price_multiplier', 6, 4)->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_rules');
    }
};
