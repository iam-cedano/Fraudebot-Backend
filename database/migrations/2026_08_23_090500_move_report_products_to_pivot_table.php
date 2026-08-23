<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['report_id', 'product_id']);
        });

        $this->copyReportProducts();

        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
        });

        $this->restoreReportProducts();

        Schema::dropIfExists('reports_products');
    }

    private function copyReportProducts(): void
    {
        $now = now();

        DB::table('reports')
            ->whereNotNull('product_id')
            ->orderBy('id')
            ->chunkById(500, function ($reports) use ($now): void {
                DB::table('reports_products')->insert(
                    $reports->map(fn ($report) => [
                        'report_id' => $report->id,
                        'product_id' => $report->product_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                );
            });
    }

    private function restoreReportProducts(): void
    {
        DB::table('reports_products')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('reports')
                        ->where('id', $row->report_id)
                        ->whereNull('product_id')
                        ->update(['product_id' => $row->product_id]);
                }
            });
    }
};
