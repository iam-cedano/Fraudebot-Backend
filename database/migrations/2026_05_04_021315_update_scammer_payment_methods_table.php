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
        DB::statement("ALTER TABLE scammer_payment_methods MODIFY COLUMN payment_type TINYINT UNSIGNED NOT NULL AFTER reference;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE scammer_payment_methods MODIFY COLUMN payment_type TINYINT UNSIGNED NOT NULL AFTER updated_at;");
    }
};
