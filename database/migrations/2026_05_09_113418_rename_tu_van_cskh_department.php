<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('departments')
            ->where('slug', 'tu-van-cskh')
            ->update(['name' => 'Phòng Tư vấn']);

        DB::table('attendance_employees')
            ->where('department', 'Phòng Tư vấn - CSKH')
            ->update(['department' => 'Phòng Tư vấn']);
    }

    public function down(): void
    {
        DB::table('departments')
            ->where('slug', 'tu-van-cskh')
            ->update(['name' => 'Phòng Tư vấn - CSKH']);

        DB::table('attendance_employees')
            ->where('department', 'Phòng Tư vấn')
            ->update(['department' => 'Phòng Tư vấn - CSKH']);
    }
};
