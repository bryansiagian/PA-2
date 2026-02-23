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
            $table->foreignId('rack_id')->constrained();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_bulky')->default(false);
            $table->integer('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
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
