<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('guest_review', 'guest_rejected', 'open', 'in_progress', 'pending_review', 'resolved') NOT NULL DEFAULT 'open'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('guest_review', 'open', 'in_progress', 'pending_review', 'resolved') NOT NULL DEFAULT 'open'");
        }
    }
};
