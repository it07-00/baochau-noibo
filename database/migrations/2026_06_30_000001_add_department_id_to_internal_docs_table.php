<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_docs', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('title')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('internal_docs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
