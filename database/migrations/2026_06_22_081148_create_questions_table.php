<?php

use App\Models\Questionnaire;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Questionnaire::class)->constrained()->cascadeOnDelete();
            $table->string('step_key');
            $table->string('field_key');
            $table->string('label');
            $table->string('input_type');
            $table->json('options_json')->nullable();
            $table->json('validation_rules_json')->nullable();
            $table->json('display_conditions_json')->nullable();
            $table->string('placeholder')->nullable();
            $table->text('helper_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['questionnaire_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
