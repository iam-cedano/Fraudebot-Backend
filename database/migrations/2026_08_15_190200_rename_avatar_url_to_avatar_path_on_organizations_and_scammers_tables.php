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
        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('avatar_url', 'avatar_path');
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->renameColumn('avatar_url', 'avatar_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('avatar_path', 'avatar_url');
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->renameColumn('avatar_path', 'avatar_url');
        });
    }
};
