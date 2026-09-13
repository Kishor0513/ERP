<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Production Manager',
            'Inventory Manager',
            'Sales Manager',
            'CRM Rep',
            'Procurement Officer',
            'QC Inspector',
            'Finance',
            'HR Officer',
            'Artisan',
            'Logistics Officer',
            'Auditor',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = [
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.approve',
            'production.view', 'production.create', 'production.edit', 'production.assign', 'production.update_status',
            'qc.inspect', 'qc.view_checklists', 'qc.manage_checklists',
            'inventory.view', 'inventory.adjust', 'inventory.manage_warehouses',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.manage',
            'raw_materials.view', 'raw_materials.create', 'raw_materials.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.approve',
            'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.send', 'quotes.approve',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send',
            'payments.view', 'payments.create', 'payments.approve',
            'shipments.view', 'shipments.create', 'shipments.track',
            'wholesale.view', 'wholesale.create', 'wholesale.edit', 'wholesale.approve',
            'crm.view_leads', 'crm.create_leads', 'crm.edit_leads', 'crm.manage_pipeline',
            'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign_roles',
            'roles.manage',
            'artisans.view', 'artisans.create', 'artisans.edit', 'artisans.manage_payroll',
            'payroll.view', 'payroll.run', 'payroll.approve',
            'settings.manage',
            'activity_log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'Super Admin' => $permissions,
            'Production Manager' => [
                'orders.view', 'production.view', 'production.create', 'production.edit', 'production.assign', 'production.update_status',
                'qc.inspect', 'qc.view_checklists',
                'products.view', 'products.edit',
                'raw_materials.view', 'raw_materials.create', 'raw_materials.edit',
                'artisans.view', 'artisans.create', 'artisans.edit',
                'reports.view',
            ],
            'Inventory Manager' => [
                'inventory.view', 'inventory.adjust', 'inventory.manage_warehouses',
                'products.view', 'products.create', 'products.edit',
                'categories.manage',
                'raw_materials.view', 'raw_materials.create', 'raw_materials.edit',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.approve',
                'reports.view',
            ],
            'Sales Manager' => [
                'orders.view', 'orders.create', 'orders.edit', 'orders.approve',
                'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.send', 'quotes.approve',
                'wholesale.view', 'wholesale.create', 'wholesale.edit', 'wholesale.approve',
                'crm.view_leads', 'crm.create_leads', 'crm.edit_leads', 'crm.manage_pipeline',
                'invoices.view', 'invoices.create', 'invoices.send',
                'products.view',
                'reports.view', 'reports.export',
            ],
            'CRM Rep' => [
                'crm.view_leads', 'crm.create_leads', 'crm.edit_leads',
                'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.send',
                'wholesale.view', 'wholesale.create', 'wholesale.edit',
                'orders.view',
                'products.view',
            ],
            'Procurement Officer' => [
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'purchase_orders.view', 'purchase_orders.create', 'purchase_orders.approve',
                'raw_materials.view', 'raw_materials.create', 'raw_materials.edit',
                'inventory.view',
                'reports.view',
            ],
            'QC Inspector' => [
                'qc.inspect', 'qc.view_checklists', 'qc.manage_checklists',
                'production.view',
                'products.view',
                'raw_materials.view',
            ],
            'Finance' => [
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.send',
                'payments.view', 'payments.create', 'payments.approve',
                'orders.view',
                'quotes.view',
                'purchase_orders.view',
                'payroll.view', 'payroll.approve',
                'reports.view', 'reports.export',
            ],
            'HR Officer' => [
                'users.view', 'users.create', 'users.edit',
                'artisans.view', 'artisans.create', 'artisans.edit', 'artisans.manage_payroll',
                'payroll.view', 'payroll.run', 'payroll.approve',
                'roles.manage',
            ],
            'Artisan' => [
                'production.view',
                'qc.view_checklists',
            ],
            'Logistics Officer' => [
                'shipments.view', 'shipments.create', 'shipments.track',
                'orders.view',
                'inventory.view',
                'purchase_orders.view',
                'reports.view',
            ],
            'Auditor' => [
                'reports.view', 'reports.export',
                'activity_log.view',
                'orders.view', 'quotes.view', 'invoices.view', 'payments.view',
                'purchase_orders.view',
                'payroll.view',
                'inventory.view',
                'production.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findByName($roleName);
            $role->syncPermissions($perms);
        }
    }
}
