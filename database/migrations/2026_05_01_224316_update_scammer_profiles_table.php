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
        Schema::table('scammer_profiles', function (Blueprint $table) {
            $table->index('name');
            $table->index('contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scammer_profiles', function (Blueprint $table) {
            $table->dropIndex('scammer_profiles_name_index');
            $table->dropIndex('scammer_profiles_contact_index');
        });
    }
};
