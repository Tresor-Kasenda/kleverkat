<?php

use App\Models\Company;
use App\Models\Offer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparison_results', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('comparison_session_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Offer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_eligible')->default(false)->index();
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('calculated_price', 12, 2)->nullable();
            $table->json('explanation_json')->nullable();
            $table->unsignedSmallInteger('rank_position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_results');
    }
};
