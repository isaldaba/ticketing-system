<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('admin_review_seen_at')->nullable()->after('resolved_at');
            $table->timestamp('staff_return_seen_at')->nullable()->after('admin_review_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'admin_review_seen_at',
                'staff_return_seen_at',
            ]);
        });
    }
};
