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
        Schema::dropIfExists('contract_milestone_files');
        Schema::create('contract_milestone_files', function (Blueprint $table) {
            $table->id();
            $table->string('contract_type', 100);
            $table->unsignedBigInteger('contract_id');
            $table->string('milestone', 50); // receiving, survey, processing, waiting_client, client_confirmed, finished
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('uploader_id');
            $table->timestamps();

            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['contract_type', 'contract_id', 'milestone'], 'cmf_contract_milestone_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_milestone_files');
    }
};
