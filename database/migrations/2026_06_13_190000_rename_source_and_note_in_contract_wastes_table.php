<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->renameColumn('source', 'info_source');
            $table->renameColumn('note', 'notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->renameColumn('info_source', 'source');
            $table->renameColumn('notes', 'note');
        });
    }
};
