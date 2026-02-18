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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('unit');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('rack_number')->nullable();
            $table->string('row_number')->nullable();
            $table->boolean('is_bulky')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
