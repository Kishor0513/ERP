<?php

use App\Http\Controllers\Api\V1\Admin\ActivityLogController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\Catalog\AttributeController;
use App\Http\Controllers\Api\V1\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Catalog\ColorChartController;
use App\Http\Controllers\Api\V1\Catalog\ProductController;
use App\Http\Controllers\Api\V1\CRM\LeadController;
use App\Http\Controllers\Api\V1\CRM\QuoteController;
use App\Http\Controllers\Api\V1\Finance\ExpenseController;
use App\Http\Controllers\Api\V1\Finance\InvoiceController;
use App\Http\Controllers\Api\V1\HR\PayrollController;
use App\Http\Controllers\Api\V1\Inventory\RawMaterialController;
use App\Http\Controllers\Api\V1\Inventory\StockMovementController;
use App\Http\Controllers\Api\V1\Inventory\WarehouseController;
use App\Http\Controllers\Api\V1\Logistics\ShipmentController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\Procurement\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Procurement\SupplierController;
use App\Http\Controllers\Api\V1\Production\ArtisanController;
use App\Http\Controllers\Api\V1\Production\ProductionOrderController;
use App\Http\Controllers\Api\V1\Production\QcInspectionController;
use App\Http\Controllers\Api\V1\Reporting\DashboardController;
use App\Http\Controllers\Api\V1\Reporting\SalesReportController;
use App\Http\Controllers\Api\V1\Sales\SalesOrderController;
use App\Http\Controllers\Api\V1\Sales\WholesaleAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/health', fn () => response()->json(['status' => 'ok']));

    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // Protected Routes
    Route::middleware(['auth:sanctum', 'org'])->group(function () {
        Route::prefix('organizations')->group(function () {
            Route::get('/', [OrganizationController::class, 'index']);
            Route::post('/', [OrganizationController::class, 'store']);
            Route::get('/{organization}', [OrganizationController::class, 'show']);
            Route::post('/{organization}/switch', [OrganizationController::class, 'switch']);
            Route::post('/{organization}/invite', [OrganizationController::class, 'invite']);
            Route::delete('/{organization}/members/{userId}', [OrganizationController::class, 'removeMember']);
        });

        Route::prefix('billing')->group(function () {
            Route::get('/', [BillingController::class, 'status']);
            Route::post('/checkout', [BillingController::class, 'checkout']);
            Route::post('/portal', [BillingController::class, 'portal']);
        });

        Route::prefix('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::get('/roles', [RoleController::class, 'index']);
            Route::get('/activity-log', [ActivityLogController::class, 'index']);
        });

        // Catalog
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::post('/{id}/variants', [ProductController::class, 'storeVariant']);
            Route::post('/bulk-import', [ProductController::class, 'bulkImport']);
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::get('/tree', [CategoryController::class, 'tree']);
            Route::get('/{id}', [CategoryController::class, 'show']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('color-chart')->group(function () {
            Route::get('/', [ColorChartController::class, 'index']);
            Route::post('/', [ColorChartController::class, 'store']);
            Route::get('/{id}', [ColorChartController::class, 'show']);
            Route::put('/{id}', [ColorChartController::class, 'update']);
            Route::delete('/{id}', [ColorChartController::class, 'destroy']);
        });

        Route::prefix('attributes')->group(function () {
            Route::get('/', [AttributeController::class, 'index']);
            Route::post('/', [AttributeController::class, 'store']);
            Route::get('/{id}', [AttributeController::class, 'show']);
            Route::put('/{id}', [AttributeController::class, 'update']);
            Route::delete('/{id}', [AttributeController::class, 'destroy']);
        });

        // Inventory
        Route::prefix('raw-materials')->group(function () {
            Route::get('/', [RawMaterialController::class, 'index']);
            Route::post('/', [RawMaterialController::class, 'store']);
            Route::get('/low-stock', [RawMaterialController::class, 'lowStock']);
            Route::get('/{id}', [RawMaterialController::class, 'show']);
            Route::put('/{id}', [RawMaterialController::class, 'update']);
            Route::delete('/{id}', [RawMaterialController::class, 'destroy']);
        });

        Route::prefix('stock-movements')->group(function () {
            Route::get('/', [StockMovementController::class, 'index']);
            Route::post('/', [StockMovementController::class, 'store']);
            Route::get('/alerts', [StockMovementController::class, 'alerts']);
        });

        Route::prefix('warehouses')->group(function () {
            Route::get('/', [WarehouseController::class, 'index']);
            Route::post('/', [WarehouseController::class, 'store']);
            Route::get('/{id}', [WarehouseController::class, 'show']);
            Route::put('/{id}', [WarehouseController::class, 'update']);
            Route::delete('/{id}', [WarehouseController::class, 'destroy']);
        });

        // Production
        Route::prefix('production-orders')->group(function () {
            Route::get('/', [ProductionOrderController::class, 'index']);
            Route::post('/', [ProductionOrderController::class, 'store']);
            Route::get('/{id}', [ProductionOrderController::class, 'show']);
            Route::put('/{id}', [ProductionOrderController::class, 'update']);
            Route::post('/{id}/assign', [ProductionOrderController::class, 'assign']);
            Route::post('/{id}/qc', [ProductionOrderController::class, 'qc']);
        });

        Route::prefix('artisans')->group(function () {
            Route::get('/', [ArtisanController::class, 'index']);
            Route::post('/', [ArtisanController::class, 'store']);
            Route::get('/{id}', [ArtisanController::class, 'show']);
            Route::put('/{id}', [ArtisanController::class, 'update']);
            Route::delete('/{id}', [ArtisanController::class, 'destroy']);
        });

        // QC
        Route::prefix('qc-inspections')->group(function () {
            Route::get('/', [QcInspectionController::class, 'index']);
            Route::post('/', [QcInspectionController::class, 'store']);
            Route::get('/{id}', [QcInspectionController::class, 'show']);
            Route::put('/{id}', [QcInspectionController::class, 'update']);
        });

        // Sales
        Route::prefix('sales-orders')->group(function () {
            Route::get('/', [SalesOrderController::class, 'index']);
            Route::post('/', [SalesOrderController::class, 'store']);
            Route::get('/{id}', [SalesOrderController::class, 'show']);
            Route::put('/{id}', [SalesOrderController::class, 'update']);
            Route::post('/{id}/ship', [SalesOrderController::class, 'ship']);
            Route::post('/{id}/cancel', [SalesOrderController::class, 'cancel']);
        });

        Route::prefix('wholesale-accounts')->group(function () {
            Route::get('/', [WholesaleAccountController::class, 'index']);
            Route::post('/', [WholesaleAccountController::class, 'store']);
            Route::get('/{id}', [WholesaleAccountController::class, 'show']);
            Route::put('/{id}', [WholesaleAccountController::class, 'update']);
            Route::post('/{id}/approve', [WholesaleAccountController::class, 'approve']);
            Route::post('/{id}/reject', [WholesaleAccountController::class, 'reject']);
        });

        // CRM
        Route::prefix('leads')->group(function () {
            Route::get('/', [LeadController::class, 'index']);
            Route::post('/', [LeadController::class, 'store']);
            Route::get('/{id}', [LeadController::class, 'show']);
            Route::put('/{id}', [LeadController::class, 'update']);
            Route::post('/{id}/convert', [LeadController::class, 'convert']);
        });

        Route::prefix('quotes')->group(function () {
            Route::get('/', [QuoteController::class, 'index']);
            Route::post('/', [QuoteController::class, 'store']);
            Route::get('/{id}', [QuoteController::class, 'show']);
            Route::put('/{id}', [QuoteController::class, 'update']);
            Route::post('/{id}/send', [QuoteController::class, 'send']);
            Route::post('/{id}/accept', [QuoteController::class, 'accept']);
            Route::post('/{id}/convert-to-order', [QuoteController::class, 'convertToOrder']);
        });

        // Procurement
        Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index']);
            Route::post('/', [PurchaseOrderController::class, 'store']);
            Route::get('/{id}', [PurchaseOrderController::class, 'show']);
            Route::put('/{id}', [PurchaseOrderController::class, 'update']);
            Route::post('/{id}/receive', [PurchaseOrderController::class, 'receive']);
        });

        Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index']);
            Route::post('/', [SupplierController::class, 'store']);
            Route::get('/{id}', [SupplierController::class, 'show']);
            Route::put('/{id}', [SupplierController::class, 'update']);
            Route::delete('/{id}', [SupplierController::class, 'destroy']);
        });

        // Logistics
        Route::prefix('shipments')->group(function () {
            Route::get('/', [ShipmentController::class, 'index']);
            Route::post('/', [ShipmentController::class, 'store']);
            Route::get('/{id}', [ShipmentController::class, 'show']);
            Route::put('/{id}', [ShipmentController::class, 'update']);
            Route::post('/{id}/dispatch', [ShipmentController::class, 'dispatch']);
            Route::post('/{id}/deliver', [ShipmentController::class, 'deliver']);
        });

        // Finance
        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceController::class, 'index']);
            Route::post('/', [InvoiceController::class, 'store']);
            Route::get('/{id}', [InvoiceController::class, 'show']);
            Route::put('/{id}', [InvoiceController::class, 'update']);
            Route::post('/{id}/record-payment', [InvoiceController::class, 'recordPayment']);
        });

        Route::prefix('expenses')->group(function () {
            Route::get('/', [ExpenseController::class, 'index']);
            Route::post('/', [ExpenseController::class, 'store']);
            Route::get('/{id}', [ExpenseController::class, 'show']);
            Route::put('/{id}', [ExpenseController::class, 'update']);
            Route::delete('/{id}', [ExpenseController::class, 'destroy']);
        });

        // HR
        Route::prefix('payroll-runs')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::post('/', [PayrollController::class, 'store']);
            Route::get('/{id}', [PayrollController::class, 'show']);
            Route::post('/{id}/approve', [PayrollController::class, 'approve']);
            Route::post('/{id}/pay', [PayrollController::class, 'pay']);
            Route::get('/{id}/export', [PayrollController::class, 'export']);
        });

        Route::prefix('piece-rates')->group(function () {
            Route::get('/', [PayrollController::class, 'pieceRates']);
            Route::post('/', [PayrollController::class, 'storePieceRate']);
            Route::get('/{id}', [PayrollController::class, 'showPieceRate']);
            Route::put('/{id}', [PayrollController::class, 'updatePieceRate']);
            Route::delete('/{id}', [PayrollController::class, 'destroyPieceRate']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'dashboard']);
            Route::get('/sales-summary', [SalesReportController::class, 'salesSummary']);
            Route::get('/production-summary', [DashboardController::class, 'productionSummary']);
            Route::get('/inventory-summary', [DashboardController::class, 'inventorySummary']);
        });
    });

    // Webhooks (no auth - validated by signature)
    Route::post('webhooks/storefront/order-created', function () {
        $payload = request()->all();
        $signature = request()->header('X-Webhook-Signature');

        // TODO: Validate webhook signature with storefront secret
        // Process order creation from storefront

        return response()->json(['status' => 'received'], 200);
    });
});
