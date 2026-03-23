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
        Schema::create('consulting_milestones_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('milestone'); // receiving, survey, processing, waiting_client, confirmed, incident
            $table->string('file_path');
            $table->unsignedBigInteger('uploader_id');
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contract_consultings')->onDelete('cascade');
            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulting_milestones_files');
    }
};
