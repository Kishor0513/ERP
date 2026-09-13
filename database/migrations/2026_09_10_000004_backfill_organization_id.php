<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function tables(): array
    {
        return [
            'categories',
            'color_chart_entries',
            'attributes',
            'attribute_values',
            'products',
            'product_variants',
            'product_images',
            'price_tiers',
            'raw_materials',
            'suppliers',
            'raw_material_batches',
            'product_boms',
            'warehouses',
            'stock_movements',
            'artisans',
            'wholesale_accounts',
            'wholesale_account_docs',
            'leads',
            'quotes',
            'quote_items',
            'sales_orders',
            'sales_order_items',
            'production_orders',
            'production_order_assignments',
            'qc_checklists',
            'qc_inspections',
            'purchase_orders',
            'purchase_order_items',
            'goods_receipt_notes',
            'goods_receipt_items',
            'shipments',
            'invoices',
            'payments',
            'expenses',
            'piece_rates',
            'payroll_runs',
            'payroll_items',
            'communication_logs',
        ];
    }

    public function up(): void
    {
        $org = Organization::first();

        if (! $org) {
            $owner = User::first();
            $org = Organization::create([
                'name' => 'Default Company',
                'slug' => 'default-company-'.strtolower(Str::random(6)),
                'owner_id' => $owner?->id,
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
            ]);
        }

        foreach ($this->tables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'organization_id')) {
                continue;
            }
            DB::table($table)->whereNull('organization_id')->update(['organization_id' => $org->id]);
        }

        DB::table('users')->whereNull('current_organization_id')->update(['current_organization_id' => $org->id]);

        $userIds = DB::table('users')->pluck('id');
        foreach ($userIds as $userId) {
            DB::table('organization_user')->updateOrInsert(
                ['organization_id' => $org->id, 'user_id' => $userId],
                ['role' => 'member', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'organization_id')) {
            DB::table('roles')->whereNull('organization_id')->update(['organization_id' => $org->id]);
        }
    }

    public function down(): void {}
};
