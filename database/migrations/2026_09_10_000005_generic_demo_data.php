<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'production@feltandyarn.com' => 'production@example.com',
            'sales@feltandyarn.com' => 'sales@example.com',
            'inventory@feltandyarn.com' => 'inventory@example.com',
            'finance@feltandyarn.com' => 'finance@example.com',
            'hr@feltandyarn.com' => 'hr@example.com',
            'qc@feltandyarn.com' => 'qc@example.com',
            'logistics@feltandyarn.com' => 'logistics@example.com',
        ];

        foreach ($map as $old => $new) {
            if (DB::table('users')->where('email', $new)->exists()) {
                DB::table('users')->where('email', $old)->delete();
            } else {
                DB::table('users')->where('email', $old)->update(['email' => $new]);
            }
        }

        foreach (['felt-yarn-default', 'acme-demo'] as $oldSlug) {
            $org = DB::table('organizations')->where('slug', $oldSlug)->first();
            if (! $org) {
                continue;
            }
            if (DB::table('organizations')->where('slug', 'demo-company')->exists()) {
                DB::table('organization_user')->where('organization_id', $org->id)->delete();
                DB::table('organizations')->where('id', $org->id)->delete();
            } else {
                DB::table('organizations')->where('id', $org->id)->update(['slug' => 'demo-company', 'name' => 'Demo Company']);
            }
        }
    }

    public function down(): void {}
};
