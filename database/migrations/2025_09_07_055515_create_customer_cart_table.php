<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
        {
            Schema::create('customer_cart', function (Blueprint $table) {
        $table->engine = 'InnoDB';

        $table->bigIncrements('id');

        // FK to users.id
        $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

        // FK to products.product_id
        $table->unsignedBigInteger('product_id');
        $table->foreign('product_id')
            ->references('product_id')
            ->on('products')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

        $table->string('size', 10);
        $table->unsignedInteger('quantity')->default(1);
        $table->timestamp('added_at')->useCurrent();

        // Indexes
        $table->unique(['user_id', 'product_id', 'size']);
        $table->index('user_id');
        $table->index('product_id');
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('customer_cart');
    }
};
