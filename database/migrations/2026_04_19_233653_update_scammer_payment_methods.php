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
        Schema::table('scammer_payment_methods', function (Blueprint $table) {
            $table->renameColumn('bank_number', 'reference');
            $table->dropColumn('iso_country');
        });

        Schema::table('scammer_payment_methods', function (Blueprint $table) {
            $table->string('reference', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scammer_payment_methods', function (Blueprint $table) {
            $table->string('bank_number', 100)->change();
            $table->string('iso_country', 5)->after('bank_number');
        });

        Schema::table('scammer_payment_methods', function (Blueprint $table) {
            $table->renameColumn('reference', 'bank_number');
        });
    }
};
