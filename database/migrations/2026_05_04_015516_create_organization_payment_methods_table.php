<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('reference', 255);
            $table->unsignedTinyInteger('payment_type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();            

            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_payment_methods');
    }
};
