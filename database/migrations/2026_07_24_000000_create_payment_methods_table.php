<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('scammer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('payment_type');
            $table->string('reference', 255);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('modified_at')->nullable();
            $table->unique(
                ['organization_id', 'reference', 'payment_type'],
                'unique_organization_payment_method',
            );
            $table->unique(
                ['scammer_id', 'reference', 'payment_type'],
                'unique_scammer_payment_method',
            );
        });

        $this->copyOrganizationPaymentMethods();
        $this->copyScammerPaymentMethods();

        Schema::dropIfExists('organization_payment_methods');
        Schema::dropIfExists('scammer_payment_methods');
    }

    public function down(): void
    {
        Schema::create('organization_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('reference', 255);
            $table->unsignedTinyInteger('payment_type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(
                ['organization_id', 'reference', 'payment_type'],
                'unique_organization_id_reference_payment_type',
            );
        });

        Schema::create('scammer_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scammer_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 255)->index();
            $table->unsignedTinyInteger('payment_type');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(
                ['scammer_id', 'reference', 'payment_type'],
                'unique_scammer_id_reference_payment_type',
            );
        });

        DB::table('payment_methods')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods): void {
                DB::table('organization_payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'organization_id' => $paymentMethod->organization_id,
                        'reference' => $paymentMethod->reference,
                        'payment_type' => $paymentMethod->payment_type,
                        'is_active' => $paymentMethod->is_active,
                        'deleted_at' => $paymentMethod->deleted_at,
                        'created_at' => $paymentMethod->created_at,
                        'updated_at' => $paymentMethod->modified_at,
                    ])->all(),
                );
            });

        DB::table('payment_methods')
            ->whereNotNull('scammer_id')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods): void {
                DB::table('scammer_payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'scammer_id' => $paymentMethod->scammer_id,
                        'reference' => $paymentMethod->reference,
                        'payment_type' => $paymentMethod->payment_type,
                        'is_active' => $paymentMethod->is_active,
                        'deleted_at' => $paymentMethod->deleted_at,
                        'created_at' => $paymentMethod->created_at,
                        'updated_at' => $paymentMethod->modified_at,
                    ])->all(),
                );
            });

        Schema::dropIfExists('payment_methods');
    }

    private function copyOrganizationPaymentMethods(): void
    {
        DB::table('organization_payment_methods')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods): void {
                DB::table('payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'organization_id' => $paymentMethod->organization_id,
                        'scammer_id' => null,
                        'payment_type' => $paymentMethod->payment_type,
                        'reference' => $paymentMethod->reference,
                        'is_active' => $paymentMethod->is_active,
                        'deleted_at' => $paymentMethod->deleted_at,
                        'created_at' => $paymentMethod->created_at,
                        'modified_at' => $paymentMethod->updated_at,
                    ])->all(),
                );
            });
    }

    private function copyScammerPaymentMethods(): void
    {
        DB::table('scammer_payment_methods')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods): void {
                DB::table('payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'organization_id' => null,
                        'scammer_id' => $paymentMethod->scammer_id,
                        'payment_type' => $paymentMethod->payment_type,
                        'reference' => $paymentMethod->reference,
                        'is_active' => $paymentMethod->is_active,
                        'deleted_at' => $paymentMethod->deleted_at,
                        'created_at' => $paymentMethod->created_at,
                        'modified_at' => $paymentMethod->updated_at,
                    ])->all(),
                );
            });
    }
};
