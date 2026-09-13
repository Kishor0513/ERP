<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = [
            [
                'invoice' => [
                    'invoice_number' => 'INV-2024-001',
                    'sales_order_id' => 1,
                    'wholesale_account_id' => null,
                    'subtotal' => 450.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 487.50,
                    'currency' => 'USD',
                    'due_date' => now()->subDays(5)->format('Y-m-d'),
                    'status' => 'paid',
                    'notes' => 'Website order invoice. Paid in full.',
                ],
                'payment' => [
                    'payment_number' => 'PAY-2024-001',
                    'method' => 'paypal',
                    'amount' => 487.50,
                    'currency' => 'USD',
                    'reference_number' => 'PP-TXN-987654',
                    'paid_at' => now()->subDays(5)->toDateTimeString(),
                    'notes' => 'Full payment via PayPal.',
                ],
            ],
            [
                'invoice' => [
                    'invoice_number' => 'INV-2024-002',
                    'sales_order_id' => 2,
                    'wholesale_account_id' => 2,
                    'subtotal' => 4375.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 4375.00,
                    'currency' => 'USD',
                    'due_date' => now()->addDays(25)->format('Y-m-d'),
                    'status' => 'sent',
                    'notes' => 'Wholesale invoice sent. Net 30 terms.',
                ],
                'payment' => null,
            ],
            [
                'invoice' => [
                    'invoice_number' => 'INV-2024-003',
                    'sales_order_id' => 3,
                    'wholesale_account_id' => 3,
                    'subtotal' => 8100.00,
                    'tax' => 0.00,
                    'discount' => 0.00,
                    'total' => 8100.00,
                    'currency' => 'USD',
                    'due_date' => now()->addDays(55)->format('Y-m-d'),
                    'status' => 'draft',
                    'notes' => 'Large wholesale order invoice. Draft pending review.',
                ],
                'payment' => null,
            ],
        ];

        foreach ($invoices as $data) {
            $invoice = Invoice::create($data['invoice']);

            if ($data['payment']) {
                $invoice->payments()->create($data['payment']);
            }
        }
    }
}
