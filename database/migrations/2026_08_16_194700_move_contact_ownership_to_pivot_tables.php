<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scammers_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scammer_id')->constrained('scammers')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['scammer_id', 'contact_id']);
        });

        Schema::create('organizations_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['organization_id', 'contact_id']);
        });

        $this->copyScammerContacts();
        $this->copyOrganizationContacts();

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropConstrainedForeignId('scammer_id');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('scammer_id')->nullable()->constrained()->cascadeOnDelete();
        });

        $this->restoreContactOwners();

        Schema::dropIfExists('scammers_contacts');
        Schema::dropIfExists('organizations_contacts');
    }

    private function copyScammerContacts(): void
    {
        $now = now();

        DB::table('contacts')
            ->whereNotNull('scammer_id')
            ->orderBy('id')
            ->chunkById(500, function ($contacts) use ($now): void {
                DB::table('scammers_contacts')->insert(
                    $contacts->map(fn ($contact) => [
                        'scammer_id' => $contact->scammer_id,
                        'contact_id' => $contact->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function copyOrganizationContacts(): void
    {
        $now = now();

        DB::table('contacts')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($contacts) use ($now): void {
                DB::table('organizations_contacts')->insert(
                    $contacts->map(fn ($contact) => [
                        'organization_id' => $contact->organization_id,
                        'contact_id' => $contact->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function restoreContactOwners(): void
    {
        DB::table('scammers_contacts')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('contacts')
                        ->where('id', $row->contact_id)
                        ->whereNull('scammer_id')
                        ->update(['scammer_id' => $row->scammer_id]);
                }
            });

        DB::table('organizations_contacts')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('contacts')
                        ->where('id', $row->contact_id)
                        ->whereNull('organization_id')
                        ->update(['organization_id' => $row->organization_id]);
                }
            });
    }
};
