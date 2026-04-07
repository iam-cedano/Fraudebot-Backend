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
        Schema::create('scammer_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scammer_id')->constrained('scammers')->onDelete('cascade');
            $table->string('bank_number', 100);
            $table->string('iso_country', 5);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scammer_payment_methods');
    }
};
