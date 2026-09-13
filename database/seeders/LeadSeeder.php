<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $leads = [
            [
                'source' => 'website',
                'company_name' => 'Pet Paradise',
                'contact_name' => 'Maria Gonzalez',
                'email' => 'maria@petparadise.com',
                'phone' => '+1-555-1001',
                'status' => 'new',
                'notes' => 'Inquired about cat caves and pet beds via website contact form.',
                'assigned_to' => 3,
            ],
            [
                'source' => 'whatsapp',
                'company_name' => 'Craft Corner USA',
                'contact_name' => 'Jennifer Adams',
                'email' => 'jen@craftcornerusa.com',
                'phone' => '+1-555-1002',
                'status' => 'contacted',
                'notes' => 'WhatsApp inquiry about bulk felt ball pricing. Follow-up scheduled.',
                'assigned_to' => 3,
            ],
            [
                'source' => 'trade_show',
                'company_name' => 'Happy Tails Pets',
                'contact_name' => 'David Kim',
                'email' => 'david@happytailspet.com',
                'phone' => '+1-555-1003',
                'status' => 'qualified',
                'notes' => 'Met at NY Pet Expo 2024. Interested in cat caves and dog beds. Budget confirmed.',
                'assigned_to' => 3,
            ],
            [
                'source' => 'referral',
                'company_name' => 'Nordic Living',
                'contact_name' => 'Anna Bergstrom',
                'email' => 'anna@nordicliving.no',
                'phone' => '+47-555-1004',
                'status' => 'converted',
                'notes' => 'Referred by Nordic Home Decor. Converted to wholesale account.',
                'assigned_to' => 3,
                'converted_to_account_id' => 3,
            ],
            [
                'source' => 'cold_outreach',
                'company_name' => 'Kids Craft Co',
                'contact_name' => 'Rachel Thompson',
                'email' => 'rachel@kidscraftco.com',
                'phone' => '+1-555-1005',
                'status' => 'new',
                'notes' => 'Cold outreach via email. Interested in felt craft supplies for kids.',
                'assigned_to' => null,
            ],
        ];

        foreach ($leads as $lead) {
            Lead::create($lead);
        }
    }
}
