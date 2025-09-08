<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── products indexes
        Schema::table('products', function (Blueprint $table) {
            // Individual indexes used by filters/order/search
            $table->index('status', 'idx_products_status');
            $table->index('category', 'idx_products_category');
            $table->index('product_name', 'idx_products_product_name');

            // Optional: if you often filter by both together, this helps
            $table->index(['category', 'status'], 'idx_products_category_status');

            // FULLTEXT (MySQL 8+ / InnoDB). If unsupported, it will be skipped safely below.
            try {
                $table->fullText(
                    ['product_name', 'description', 'category'],
                    'ft_products_name_desc_cat'
                );
            } catch (\Throwable $e) {
                // Ignore if the DB engine/version doesn't support FULLTEXT here.
            }
        });

        // ── product_sizes indexes
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->index('product_id', 'idx_ps_product_id');
            $table->index(['product_id', 'size'], 'idx_ps_product_id_size');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop in reverse; each wrapped to avoid failing if missing
            try { $table->dropIndex('idx_products_status'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_products_category'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_products_product_name'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_products_category_status'); } catch (\Throwable $e) {}
            try { $table->dropFullText('ft_products_name_desc_cat'); } catch (\Throwable $e) {}
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            try { $table->dropIndex('idx_ps_product_id'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_ps_product_id_size'); } catch (\Throwable $e) {}
        });
    }
};
