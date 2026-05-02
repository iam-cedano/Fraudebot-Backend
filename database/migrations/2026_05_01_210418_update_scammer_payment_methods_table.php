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
            $table->index('reference');
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->fullText('name');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->fullText('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scammer_payment_methods', function (Blueprint $table) {
            $table->dropIndex('scammer_payment_methods_reference_index');
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->dropFullText('scammers_name_fulltext');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropFullText('organizations_name_fulltext');
        });
    }
};
