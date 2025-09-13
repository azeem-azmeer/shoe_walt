<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Your enum already has: 'Pending','Confirmed','Cancelled'
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status
            ENUM('Pending','Confirmed','Cancelled')
            NOT NULL DEFAULT 'Confirmed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status
            ENUM('Pending','Confirmed','Cancelled')
            NOT NULL DEFAULT 'Pending'
        ");
    }
};
