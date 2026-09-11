<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
        DB::table('users')->where('role', 'admin')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        // Role and flag consistency should not be intentionally reversed.
    }
};
