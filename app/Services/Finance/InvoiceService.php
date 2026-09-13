<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function getAll(array $filters = [])
    {
        $query = Invoice::with(['salesOrder', 'wholesaleAccount', 'payments']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        if (isset($filters['wholesale_account_id'])) {
            $query->where('wholesale_account_id', $filters['wholesale_account_id']);
        }

        if (isset($filters['search'])) {
            $query->where('invoice_number', 'like', "%{$filters['search']}%");
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Invoice
    {
        return Invoice::with([
            'salesOrder.items.productVariant.product',
            'wholesaleAccount',
            'payments',
        ])->findOrFail($id);
    }

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['status'] = $data['status'] ?? 'draft';

            if (isset($data['sales_order_id']) && ! isset($data['wholesale_account_id'])) {
                $order = SalesOrder::findOrFail($data['sales_order_id']);
                $data['wholesale_account_id'] = $order->wholesale_account_id;
            }

            $invoice = Invoice::create($data);

            return $invoice->load(['salesOrder', 'wholesaleAccount', 'payments']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            throw new \Exception('Cannot update a '.$invoice->status.' invoice.');
        }

        $invoice->update($data);

        return $invoice->fresh(['salesOrder', 'wholesaleAccount', 'payments']);
    }

    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Invoice is already fully paid.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            $data['payment_number'] = $this->generatePaymentNumber();
            $data['invoice_id'] = $invoice->id;
            $data['paid_at'] = $data['paid_at'] ?? now();

            $payment = Payment::create($data);

            $totalPaid = $invoice->payments()->sum('amount') + $data['amount'];

            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            }

            return $payment;
        });
    }

    public function cancel(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'paid') {
            throw new \Exception('Cannot cancel a paid invoice.');
        }

        $invoice->update(['status' => 'cancelled']);

        return $invoice;
    }

    public function delete(Invoice $invoice): bool
    {
        if ($invoice->payments()->exists()) {
            throw new \Exception('Cannot delete invoice with payments.');
        }

        return $invoice->delete();
    }

    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-'.now()->format('Ym');
        $lastPayment = Payment::where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->first();

        if ($lastPayment && preg_match('/(\d+)$/', $lastPayment->payment_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym');
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->first();

        if ($lastInvoice && preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $prefix.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
