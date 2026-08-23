<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicate('contacts', ['platform', 'reference'], [
            'scammers_contacts' => 'contact_id',
            'organizations_contacts' => 'contact_id',
        ]);
        $this->deduplicate('payment_methods', ['type', 'reference'], [
            'scammers_payment_methods' => 'payment_method_id',
            'organizations_payment_methods' => 'payment_method_id',
        ]);

        Schema::table('contacts', function (Blueprint $table) {
            $table->unique(['platform', 'reference'], 'contacts_platform_reference_unique');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unique(['type', 'reference'], 'payment_methods_type_reference_unique');
        });

        Schema::table('scammers_contacts', fn (Blueprint $table) => $table->index('contact_id'));
        Schema::table('organizations_contacts', fn (Blueprint $table) => $table->index('contact_id'));
        Schema::table('scammers_payment_methods', fn (Blueprint $table) => $table->index('payment_method_id'));
        Schema::table('organizations_payment_methods', fn (Blueprint $table) => $table->index('payment_method_id'));
        Schema::table('scammers_reports', fn (Blueprint $table) => $table->index('report_id'));
        Schema::table('organizations_reports', fn (Blueprint $table) => $table->index('report_id'));
        Schema::table('reports_products', fn (Blueprint $table) => $table->index('product_id'));
        Schema::table('reports', fn (Blueprint $table) => $table->index(['is_active', 'created_at']));
    }

    public function down(): void
    {
        Schema::table('contacts', fn (Blueprint $table) => $table->dropUnique('contacts_platform_reference_unique'));
        Schema::table('payment_methods', fn (Blueprint $table) => $table->dropUnique('payment_methods_type_reference_unique'));
        Schema::table('scammers_contacts', fn (Blueprint $table) => $table->dropIndex(['contact_id']));
        Schema::table('organizations_contacts', fn (Blueprint $table) => $table->dropIndex(['contact_id']));
        Schema::table('scammers_payment_methods', fn (Blueprint $table) => $table->dropIndex(['payment_method_id']));
        Schema::table('organizations_payment_methods', fn (Blueprint $table) => $table->dropIndex(['payment_method_id']));
        Schema::table('scammers_reports', fn (Blueprint $table) => $table->dropIndex(['report_id']));
        Schema::table('organizations_reports', fn (Blueprint $table) => $table->dropIndex(['report_id']));
        Schema::table('reports_products', fn (Blueprint $table) => $table->dropIndex(['product_id']));
        Schema::table('reports', fn (Blueprint $table) => $table->dropIndex(['is_active', 'created_at']));
    }

    private function deduplicate(string $table, array $identity, array $pivots): void
    {
        $duplicates = DB::table($table)
            ->select([...$identity, DB::raw('COUNT(*) as aggregate')])
            ->groupBy($identity)
            ->having('aggregate', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $query = DB::table($table);
            foreach ($identity as $column) {
                $query->where($column, $duplicate->{$column});
            }

            $rows = $query->orderByRaw('deleted_at IS NULL DESC')->orderBy('id')->get();
            $canonical = $rows->shift();

            if ($rows->contains(fn ($row) => $row->deleted_at === null)) {
                DB::table($table)->where('id', $canonical->id)->update(['deleted_at' => null]);
            }

            foreach ($rows as $row) {
                foreach ($pivots as $pivotTable => $foreignKey) {
                    $links = DB::table($pivotTable)->where($foreignKey, $row->id)->get();
                    foreach ($links as $link) {
                        $values = (array) $link;
                        unset($values['id']);
                        $values[$foreignKey] = $canonical->id;
                        DB::table($pivotTable)->insertOrIgnore($values);
                    }
                    DB::table($pivotTable)->where($foreignKey, $row->id)->delete();
                }

                DB::table($table)->where('id', $row->id)->delete();
            }
        }
    }
};
