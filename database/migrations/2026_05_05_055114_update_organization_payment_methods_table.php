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
        Schema::table('organization_payment_methods', function (Blueprint $table) {
            $table->unique(['organization_id', 'reference', 'payment_type'], 'unique_organization_id_reference_payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_payment_methods', function (Blueprint $table) {
            $table->dropUnique('unique_organization_id_reference_payment_type');
        });
    }
};
