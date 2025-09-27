<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create table if it doesn't exist
        if (!Schema::hasTable('wishlist')) {
            Schema::create('wishlist', function (Blueprint $table) {
                $table->id();

                // users.id (Laravel default is BIGINT UNSIGNED)
                $table->foreignId('user_id')
                      ->constrained()           // users.id
                      ->cascadeOnDelete();

                // products.product_id (BIGINT UNSIGNED in your schema)
                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                      ->references('product_id') // <-- reference real PK on products
                      ->on('products')
                      ->cascadeOnDelete();

                $table->timestamp('added_at')->useCurrent();

                // Prevent duplicates per user/product
                $table->unique(['user_id', 'product_id']);
            });
        } else {
            // If table exists, make sure required columns/constraints are there
            Schema::table('wishlist', function (Blueprint $table) {
                if (!Schema::hasColumn('wishlist', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->after('user_id');
                }
                if (!Schema::hasColumn('wishlist', 'added_at')) {
                    $table->timestamp('added_at')->useCurrent()->after('product_id');
                }

                // Add FK only if it's missing
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails('wishlist');
                if (!$doctrineTable->hasForeignKey('wishlist_product_id_foreign')) {
                    $table->foreign('product_id')
                          ->references('product_id')
                          ->on('products')
                          ->cascadeOnDelete();
                }

                // Ensure unique constraint exists
                if (!$doctrineTable->hasIndex('wishlist_user_id_product_id_unique')) {
                    $table->unique(['user_id', 'product_id']);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('wishlist', function (Blueprint $table) {
            // Drop FK/indexes defensively
            try { $table->dropForeign('wishlist_product_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropUnique('wishlist_user_id_product_id_unique'); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('wishlist');
    }
};
