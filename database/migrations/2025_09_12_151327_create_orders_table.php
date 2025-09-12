<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();                                       // BIGINT UNSIGNED AI
            $table->foreignId('user_id')->constrained()         // -> references users.id
                  ->cascadeOnDelete();
            $table->string('street_address', 255);
            $table->enum('status', ['Pending','Confirmed','Cancelled'])->default('Pending');
            $table->decimal('total', 10, 2);
            $table->timestamp('order_date')->useCurrent();      // same as CURRENT_TIMESTAMP
            $table->timestamps();

            // (optional) keep collation explicit if you want to mirror your DB defaults
            // $table->charset('utf8mb4');
            // $table->collation('utf8mb4_unicode_ci');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
