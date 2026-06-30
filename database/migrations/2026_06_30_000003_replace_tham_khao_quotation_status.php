<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quotations')
            ->where('status', 'Tham khảo')
            ->update(['status' => 'BG tiềm năng']);
    }

    public function down(): void
    {
        DB::table('quotations')
            ->where('status', 'BG tiềm năng')
            ->update(['status' => 'Tham khảo']);
    }
};
