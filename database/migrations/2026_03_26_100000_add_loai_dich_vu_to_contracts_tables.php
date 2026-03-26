<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->string('loai_dich_vu')->nullable()->after('content');
        });

        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->string('loai_dich_vu')->nullable()->after('notes');
        });

        Schema::table('contract_commercials', function (Blueprint $table) {
            $table->string('loai_dich_vu')->nullable()->after('notes');
        });

        Schema::table('contract_projects', function (Blueprint $table) {
            $table->string('loai_dich_vu')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->dropColumn('loai_dich_vu');
        });
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->dropColumn('loai_dich_vu');
        });
        Schema::table('contract_commercials', function (Blueprint $table) {
            $table->dropColumn('loai_dich_vu');
        });
        Schema::table('contract_projects', function (Blueprint $table) {
            $table->dropColumn('loai_dich_vu');
        });
    }
};
