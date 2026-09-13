<?php

namespace App\Services\CRM;

use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    private const VALID_STATUS_TRANSITIONS = [
        'draft' => ['sent', 'cancelled'],
        'sent' => ['accepted', 'rejected', 'expired'],
        'accepted' => [],
        'rejected' => [],
        'expired' => [],
        'cancelled' => [],
    ];

    public function getAll(array $filters = [])
    {
        $query = Quote::with(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['wholesale_account_id'])) {
            $query->where('wholesale_account_id', $filters['wholesale_account_id']);
        }

        if (isset($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }

        if (isset($filters['search'])) {
            $query->where('quote_number', 'like', "%{$filters['search']}%");
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Quote
    {
        return Quote::with([
            'wholesaleAccount',
            'lead',
            'creator',
            'items.productVariant.product',
            'items.productVariant.colorChartEntry',
        ])->findOrFail($id);
    }

    public function create(array $data): Quote
    {
        return DB::transaction(function () use ($data) {
            $data['quote_number'] = $this->generateQuoteNumber();
            $data['created_by'] = auth()->id();
            $data['status'] = $data['status'] ?? 'draft';
            $data['valid_until'] = $data['valid_until'] ?? now()->addDays(30);

            $quote = Quote::create($data);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty'] * $itemData['unit_price'];
                    $quote->items()->create($itemData);
                }

                $this->recalculateTotals($quote);
            }

            return $quote->load(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
        });
    }

    public function update(Quote $quote, array $data): Quote
    {
        if (in_array($quote->status, ['accepted', 'rejected', 'cancelled'])) {
            throw new \Exception('Cannot update a '.$quote->status.' quote.');
        }

        return DB::transaction(function () use ($quote, $data) {
            $quote->update(collect($data)->except('items')->toArray());

            if (isset($data['items'])) {
                $quote->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = $itemData['qty'] * $itemData['unit_price'];
                    $quote->items()->create($itemData);
                }

                $this->recalculateTotals($quote);
            }

            return $quote->fresh(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
        });
    }

    public function updateStatus(Quote $quote, string $newStatus): Quote
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$quote->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$quote->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        $quote->update(['status' => $newStatus]);

        return $quote->fresh(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
    }

    public function convertToOrder(Quote $quote): SalesOrder
    {
        if ($quote->status !== 'accepted') {
            throw new \Exception('Only accepted quotes can be converted to orders.');
        }

        if ($quote->is_expired) {
            throw new \Exception('Cannot convert an expired quote.');
        }

        return DB::transaction(function () use ($quote) {
            $orderData = [
                'order_number' => $this->generateOrderNumber(),
                'channel' => 'wholesale',
                'wholesale_account_id' => $quote->wholesale_account_id,
                'lead_id' => $quote->lead_id,
                'status' => 'draft',
                'currency' => $quote->currency,
                'subtotal' => $quote->subtotal,
                'tax' => $quote->tax,
                'discount' => $quote->discount,
                'total' => $quote->total,
                'notes' => "Converted from Quote #{$quote->quote_number}",
                'created_by' => auth()->id(),
            ];

            $order = SalesOrder::create($orderData);

            foreach ($quote->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]);
            }

            $quote->update(['status' => 'converted']);

            return $order->load(['wholesaleAccount', 'lead', 'creator', 'items.productVariant.product']);
        });
    }

    public function delete(Quote $quote): bool
    {
        if (in_array($quote->status, ['accepted', 'converted'])) {
            throw new \Exception('Cannot delete an accepted or converted quote.');
        }

        return DB::transaction(function () use ($quote) {
            $quote->items()->delete();

            return $quote->delete();
        });
    }

    protected function recalculateTotals(Quote $quote): void
    {
        $subtotal = $quote->items->sum('total');
        $tax = $subtotal * 0.1;
        $discount = $quote->discount ?? 0;
        $total = $subtotal + $tax - $discount;

        $quote->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
    }

    protected function generateQuoteNumber(): string
    {
        $prefix = 'QT-'.now()->format('Ym');
        $lastQuote = Quote::where('quote_number', 'like', $prefix.'%')
            ->orderByDesc('quote_number')
            ->first();

        if ($lastQuote && preg_match('/(\d+)$/', $lastQuote->quote_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'SO-'.now()->format('Ym');
        $lastOrder = SalesOrder::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
