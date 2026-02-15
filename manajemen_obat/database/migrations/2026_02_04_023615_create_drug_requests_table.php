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
        Schema::create('drug_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Customer
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'shipping', 'completed'])->default('pending');
            $table->enum('request_type', ['delivery', 'self_pickup'])->default('delivery');
            $table->enum('required_vehicle', ['motorcycle', 'car'])->default('motorcycle');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_requests');
    }
};
