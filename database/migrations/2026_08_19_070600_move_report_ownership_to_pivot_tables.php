<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scammers_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scammer_id')->constrained('scammers')->cascadeOnDelete();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['scammer_id', 'report_id']);
        });

        Schema::create('organizations_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'report_id']);
        });

        $this->copyScammerReports();
        $this->copyOrganizationReports();

        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropConstrainedForeignId('scammer_id');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained('organizations');
            $table->foreignId('scammer_id')->nullable()->constrained('scammers');
        });

        $this->restoreReportOwners();

        Schema::dropIfExists('scammers_reports');
        Schema::dropIfExists('organizations_reports');
    }

    private function copyScammerReports(): void
    {
        $now = now();

        DB::table('reports')
            ->whereNotNull('scammer_id')
            ->orderBy('id')
            ->chunkById(500, function ($reports) use ($now): void {
                DB::table('scammers_reports')->insert(
                    $reports->map(fn ($report) => [
                        'scammer_id' => $report->scammer_id,
                        'report_id' => $report->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function copyOrganizationReports(): void
    {
        $now = now();

        DB::table('reports')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($reports) use ($now): void {
                DB::table('organizations_reports')->insert(
                    $reports->map(fn ($report) => [
                        'organization_id' => $report->organization_id,
                        'report_id' => $report->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function restoreReportOwners(): void
    {
        DB::table('scammers_reports')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('reports')
                        ->where('id', $row->report_id)
                        ->whereNull('scammer_id')
                        ->update(['scammer_id' => $row->scammer_id]);
                }
            });

        DB::table('organizations_reports')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('reports')
                        ->where('id', $row->report_id)
                        ->whereNull('organization_id')
                        ->update(['organization_id' => $row->organization_id]);
                }
            });
    }
};
