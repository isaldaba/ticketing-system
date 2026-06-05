<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable()->after('concern');
            $table->text('admin_note')->nullable()->after('resolution_note');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('resolved_at')->nullable()->after('submitted_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('open', 'in_progress', 'pending_review', 'resolved') NOT NULL DEFAULT 'open'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('open', 'resolved') NOT NULL DEFAULT 'open'");
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'assigned_to',
                'resolution_note',
                'admin_note',
                'submitted_at',
                'resolved_at',
            ]);
        });
    }
};
