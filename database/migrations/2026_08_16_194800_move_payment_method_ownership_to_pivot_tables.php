<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scammers_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scammer_id')->constrained('scammers')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['scammer_id', 'payment_method_id'], 'scammer_payment_method_unique');
        });

        Schema::create('organizations_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['organization_id', 'payment_method_id'], 'org_payment_method_unique');
        });

        $this->copyScammerPaymentMethods();
        $this->copyOrganizationPaymentMethods();

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['scammer_id']);
            $table->dropUnique('unique_organization_payment_method');
            $table->dropUnique('unique_scammer_payment_method');
            $table->dropColumn(['organization_id', 'scammer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('scammer_id')->nullable()->constrained()->cascadeOnDelete();
        });

        $this->restorePaymentMethodOwners();

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unique(
                ['organization_id', 'reference', 'payment_type'],
                'unique_organization_payment_method',
            );
            $table->unique(
                ['scammer_id', 'reference', 'payment_type'],
                'unique_scammer_payment_method',
            );
        });

        Schema::dropIfExists('scammers_payment_methods');
        Schema::dropIfExists('organizations_payment_methods');
    }

    private function copyScammerPaymentMethods(): void
    {
        $now = now();

        DB::table('payment_methods')
            ->whereNotNull('scammer_id')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods) use ($now): void {
                DB::table('scammers_payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'scammer_id' => $paymentMethod->scammer_id,
                        'payment_method_id' => $paymentMethod->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function copyOrganizationPaymentMethods(): void
    {
        $now = now();

        DB::table('payment_methods')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($paymentMethods) use ($now): void {
                DB::table('organizations_payment_methods')->insert(
                    $paymentMethods->map(fn ($paymentMethod) => [
                        'organization_id' => $paymentMethod->organization_id,
                        'payment_method_id' => $paymentMethod->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function restorePaymentMethodOwners(): void
    {
        DB::table('scammers_payment_methods')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('payment_methods')
                        ->where('id', $row->payment_method_id)
                        ->whereNull('scammer_id')
                        ->update(['scammer_id' => $row->scammer_id]);
                }
            });

        DB::table('organizations_payment_methods')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('payment_methods')
                        ->where('id', $row->payment_method_id)
                        ->whereNull('organization_id')
                        ->update(['organization_id' => $row->organization_id]);
                }
            });
    }
};
