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
        Schema::dropIfExists('contract_workflow_steps');
        Schema::create('contract_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->morphs('contract'); // contract_type, contract_id
            $table->unsignedBigInteger('user_id');
            $table->string('step_name'); // receiving, survey, processing, waiting_client, client_confirmed, finished
            $table->string('action'); // start, upload, complete
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['contract_type', 'contract_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_workflow_steps');
    }
};
