<?php

namespace Database\Seeders;

use App\Models\GoodsReceiptItem;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoodsReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $receiver = User::first();
        $orders = PurchaseOrder::with('items')->take(2)->get();

        if ($orders->isEmpty()) {
            return;
        }

        foreach ($orders as $i => $order) {
            $grn = GoodsReceiptNote::create([
                'grn_number' => 'GRN-2024-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'purchase_order_id' => $order->id,
                'received_by' => $receiver?->id,
                'received_at' => now()->subDays(3 - $i),
                'notes' => 'Checked against PO, all items verified.',
            ]);

            foreach ($order->items as $item) {
                GoodsReceiptItem::create([
                    'grn_id' => $grn->id,
                    'purchase_order_item_id' => $item->id,
                    'qty_received' => $item->quantity ?? $item->qty ?? 1,
                    'batch_no' => 'B-2024-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
