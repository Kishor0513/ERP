<?php

namespace App\Services\Logistics;

use App\Models\SalesOrder;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    private const VALID_STATUS_TRANSITIONS = [
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['dispatched', 'cancelled'],
        'dispatched' => ['in_transit'],
        'in_transit' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function getAll(array $filters = [])
    {
        $query = Shipment::with(['salesOrder', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('shipment_number', 'like', "%{$filters['search']}%")
                    ->orWhere('tracking_no', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Shipment
    {
        return Shipment::with(['salesOrder.items.productVariant.product', 'creator'])->findOrFail($id);
    }

    public function create(array $data): Shipment
    {
        return DB::transaction(function () use ($data) {
            $data['shipment_number'] = $this->generateShipmentNumber();
            $data['created_by'] = auth()->id();
            $data['status'] = $data['status'] ?? 'preparing';

            $shipment = Shipment::create($data);

            $this->updateOrderShipmentStatus($data['sales_order_id']);

            return $shipment->load(['salesOrder', 'creator']);
        });
    }

    public function update(Shipment $shipment, array $data): Shipment
    {
        if (in_array($shipment->status, ['delivered', 'cancelled'])) {
            throw new \Exception('Cannot update a '.$shipment->status.' shipment.');
        }

        $shipment->update($data);

        return $shipment->fresh(['salesOrder', 'creator']);
    }

    public function updateStatus(Shipment $shipment, string $newStatus): Shipment
    {
        $validTransitions = self::VALID_STATUS_TRANSITIONS[$shipment->status] ?? [];

        if (! in_array($newStatus, $validTransitions)) {
            throw new \Exception(
                "Cannot transition from '{$shipment->status}' to '{$newStatus}'. Valid transitions: ".
                implode(', ', $validTransitions)
            );
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'delivered') {
            $updateData['actual_arrival'] = now();
        }

        $shipment->update($updateData);

        $this->updateOrderShipmentStatus($shipment->sales_order_id);

        return $shipment->fresh(['salesOrder', 'creator']);
    }

    public function markDispatched(Shipment $shipment): Shipment
    {
        return $this->updateStatus($shipment, 'dispatched');
    }

    public function markDelivered(Shipment $shipment): Shipment
    {
        return $this->updateStatus($shipment, 'delivered');
    }

    public function generateCustomsDocuments(Shipment $shipment): array
    {
        $order = $shipment->salesOrder->load(['items.productVariant.product', 'wholesaleAccount']);

        $documents = [
            'commercial_invoice' => [
                'shipment_number' => $shipment->shipment_number,
                'invoice_date' => now()->format('Y-m-d'),
                'seller' => config('app.company_name', 'Novera'),
                'buyer' => $order->wholesaleAccount?->company_name ?? 'N/A',
                'incoterm' => $shipment->incoterm,
                'items' => $order->items->map(fn ($item) => [
                    'description' => $item->productVariant->product->name,
                    'hs_code' => $item->productVariant->product->hs_code,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ]),
                'total_value' => $shipment->declared_value,
                'currency' => $shipment->currency,
            ],
            'packing_list' => [
                'shipment_number' => $shipment->shipment_number,
                'date' => now()->format('Y-m-d'),
                'items' => $order->items->map(fn ($item) => [
                    'description' => $item->productVariant->product->name,
                    'qty' => $item->qty,
                    'weight' => $item->productVariant->product->weight_grams * $item->qty / 1000,
                ]),
            ],
        ];

        if ($shipment->certificate_of_origin) {
            $documents['certificate_of_origin'] = [
                'shipment_number' => $shipment->shipment_number,
                'country_of_origin' => config('app.country_of_origin', 'India'),
                'date' => now()->format('Y-m-d'),
            ];
        }

        if ($shipment->fair_trade_doc) {
            $documents['fair_trade_certificate'] = [
                'shipment_number' => $shipment->shipment_number,
                'date' => now()->format('Y-m-d'),
                'certification_body' => 'Fair Trade Organization',
            ];
        }

        return $documents;
    }

    public function delete(Shipment $shipment): bool
    {
        if (! in_array($shipment->status, ['preparing'])) {
            throw new \Exception('Only preparing shipments can be deleted.');
        }

        return DB::transaction(function () use ($shipment) {
            $salesOrderId = $shipment->sales_order_id;
            $shipment->delete();
            $this->updateOrderShipmentStatus($salesOrderId);

            return true;
        });
    }

    protected function updateOrderShipmentStatus(int $salesOrderId): void
    {
        $order = SalesOrder::findOrFail($salesOrderId);
        $shipments = $order->shipments;

        if ($shipments->isEmpty()) {
            return;
        }

        $allDelivered = $shipments->every(fn ($s) => $s->status === 'delivered');
        $anyDispatched = $shipments->contains(fn ($s) => in_array($s->status, ['dispatched', 'in_transit', 'delivered']));

        if ($allDelivered) {
            $order->update(['status' => 'delivered']);
        } elseif ($anyDispatched) {
            $order->update(['status' => 'shipped']);
        }
    }

    protected function generateShipmentNumber(): string
    {
        $prefix = 'SHP-'.now()->format('Ym');
        $lastShipment = Shipment::where('shipment_number', 'like', $prefix.'%')
            ->orderByDesc('shipment_number')
            ->first();

        if ($lastShipment && preg_match('/(\d+)$/', $lastShipment->shipment_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
