<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If the table is already in MySQL, skip creation so migrate won't fail.
        if (!Schema::hasTable('wishlist')) {
            Schema::create('wishlist', function (Blueprint $table) {
                // Primary key
                $table->id(); // big integer auto-increment

                // Foreign keys
                $table->foreignId('user_id')
                      ->constrained()            // references users.id
                      ->cascadeOnDelete();

                // If your products PK is 'id' (default), this is correct:
                $table->foreignId('product_id')
                      ->constrained('products')  // references products.id
                      ->cascadeOnDelete();

                // If your products PK is 'product_id' instead, comment the two
                // lines above and use the two lines below:
                // $table->unsignedBigInteger('product_id');
                // $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();

                // Data columns
                $table->timestamp('added_at')->useCurrent();

                // Prevent duplicates per user
                $table->unique(['user_id', 'product_id']);
            });
        } else {
            // Table exists already — optionally ensure the expected columns exist.
            // (Only adds 'added_at' if it's missing; safe to run multiple times.)
            if (!Schema::hasColumn('wishlist', 'added_at')) {
                Schema::table('wishlist', function (Blueprint $table) {
                    $table->timestamp('added_at')->useCurrent()->after('product_id');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
