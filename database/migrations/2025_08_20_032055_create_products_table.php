<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // primary key named product_id (as you asked)
            $table->id('product_id');

            $table->string('product_name');
            $table->decimal('price', 10, 2);

            // store category as text (change to foreignId if you have a categories table)
            $table->string('category');

            // images (store file paths/URLs)
            $table->string('main_image')->nullable();
            $table->string('view_image1')->nullable();
            $table->string('view_image2')->nullable();
            $table->string('view_image3')->nullable();
            $table->string('view_image4')->nullable();

            $table->unsignedInteger('sold_pieces')->default(0);
            $table->unsignedInteger('stock')->default(0);

            $table->timestamps();

            $table->index('category');
            $table->index('product_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


