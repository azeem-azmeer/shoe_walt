<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add it if it doesn't exist yet
        if (!Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('firebase_uid')
                      ->nullable()
                      ->unique()
                      ->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'firebase_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('firebase_uid');
            });
        }
    }
};
