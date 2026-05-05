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
        Schema::rename('og_access_points', 'organization_access_points');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('organization_access_points', 'og_access_points');
    }
};
