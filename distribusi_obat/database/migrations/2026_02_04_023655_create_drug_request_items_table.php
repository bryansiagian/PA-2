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
        Schema::create('drug_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_request_id')->constrained('drug_requests')->onDelete('cascade');
            $table->foreignId('drug_id')->nullable()->constrained('drugs')->onDelete('cascade');
            $table->string('custom_drug_name')->nullable();
            $table->string('custom_unit')->nullable();

            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_request_items');
    }
};