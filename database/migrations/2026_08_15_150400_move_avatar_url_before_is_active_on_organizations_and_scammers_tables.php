<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('avatar_url', 255)->nullable()->after('country')->change();
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->string('avatar_url', 255)->nullable()->after('country')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('avatar_url', 255)->nullable()->after('updated_at')->change();
        });

        Schema::table('scammers', function (Blueprint $table) {
            $table->string('avatar_url', 255)->nullable()->after('updated_at')->change();
        });
    }
};
