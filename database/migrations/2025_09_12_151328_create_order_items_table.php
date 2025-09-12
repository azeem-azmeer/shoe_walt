<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();                                                // BIGINT UNSIGNED AI
            $table->foreignId('order_id')->constrained('orders')         // FK -> orders.id
                  ->cascadeOnDelete();

            // Your products table uses product_id as the PK
            $table->unsignedBigInteger('product_id');
            $table->string('size', 10);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);

            // FK to products.product_id
            $table->foreign('product_id')
                  ->references('product_id')->on('products')
                  ->restrictOnDelete();

            // $table->charset('utf8mb4');
            // $table->collation('utf8mb4_unicode_ci');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
