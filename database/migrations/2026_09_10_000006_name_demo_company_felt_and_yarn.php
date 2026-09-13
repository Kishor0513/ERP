<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizations')->where('slug', 'demo-company')->update(['slug' => 'felt-and-yarn', 'name' => 'Felt and Yarn']);
        DB::table('organizations')->where('slug', 'acme-demo')->update(['slug' => 'felt-and-yarn', 'name' => 'Felt and Yarn']);
    }

    public function down(): void {}
};
