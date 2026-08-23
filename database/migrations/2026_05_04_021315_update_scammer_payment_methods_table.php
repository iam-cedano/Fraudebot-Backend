<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE scammer_payment_methods MODIFY COLUMN payment_type TINYINT UNSIGNED NOT NULL AFTER reference;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE scammer_payment_methods MODIFY COLUMN payment_type TINYINT UNSIGNED NOT NULL AFTER updated_at;');
    }
};
