<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['contact']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('contact', 'reference');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('reference', 255)->change();
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['reference']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('reference', 'contact');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('contact', 100)->change();
            $table->index('contact');
        });
    }
};
