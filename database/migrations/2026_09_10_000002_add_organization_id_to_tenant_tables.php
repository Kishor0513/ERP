<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            'roles',
        ];
    }

    public function up(): void
    {
        foreach ($this->tables() as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'organization_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $t->index('organization_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'organization_id')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('organization_id');
            });
        }
    }
};
