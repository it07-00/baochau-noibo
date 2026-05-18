<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Quan trắc môi trường lao động, Phân loại lao động')
            ->update(['loai_dich_vu' => 'Quan trắc môi trường lao động']);
    }

    public function down(): void
    {
        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Quan trắc môi trường lao động')
            ->update(['loai_dich_vu' => 'Quan trắc môi trường lao động, Phân loại lao động']);
    }
};
