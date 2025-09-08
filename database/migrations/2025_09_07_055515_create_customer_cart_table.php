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

            // FK to products.product_id (or 'id' if your PK is id)
            $table->foreignId('product_id')
            
                ->constrained('products', 'product_id') // change 2nd arg to 'id' if your PK is id
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('size', 10);
            $table->unsignedInteger('quantity')->default(1);

            // Match your spec: separate timestamp for when the item was added
            $table->timestamp('added_at')->useCurrent();

            // Helpful indexes / guard against duplicates of the same (user, product, size)
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
