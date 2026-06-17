<?php

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
            $table->foreignId('manager_id')
                ->nullable()
                ->after('team_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('contact_name')->nullable()->after('support_phone');
            $table->string('address_line_1')->nullable()->after('contact_name');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('postal_code')->nullable()->after('city');
            $table->string('country')->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn([
                'contact_name',
                'address_line_1',
                'address_line_2',
                'city',
                'postal_code',
                'country',
            ]);
        });
    }
};
