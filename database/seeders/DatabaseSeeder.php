<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            CategorySeeder::class,
            ColorChartSeeder::class,
            AttributeSeeder::class,
            WarehouseSeeder::class,
            SupplierSeeder::class,
            RawMaterialSeeder::class,
            ProductSeeder::class,
            ArtisanSeeder::class,
            WholesaleAccountSeeder::class,
            LeadSeeder::class,
            QuoteSeeder::class,
            SalesOrderSeeder::class,
            ProductionOrderSeeder::class,
            PurchaseOrderSeeder::class,
            StockMovementSeeder::class,
            ShipmentSeeder::class,
            InvoiceSeeder::class,
            PieceRateSeeder::class,
            PayrollSeeder::class,
            QcChecklistSeeder::class,
            QcInspectionSeeder::class,
            GoodsReceiptSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
