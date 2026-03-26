<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Change status enum to include all 6 states
        // MySQL requires dropping and recreating the enum
        DB::statement("ALTER TABLE parking_slots MODIFY COLUMN status ENUM(
            'available',
            'reserved',
            'booked',
            'occupied',
            'completed',
            'blocked',
            'maintenance'
        ) NOT NULL DEFAULT 'available'");

        Schema::table('parking_slots', function (Blueprint $table) {
            // Who reserved this slot (null when available)
            $table->foreignId('reserved_by_user_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            // When the reservation expires (null when not reserved)
            $table->timestamp('reserved_until')
                ->nullable()
                ->after('reserved_by_user_id');

            $table->index(['parking_space_id', 'status'], 'slots_space_status_idx');
            $table->index('reserved_until', 'slots_reserved_until_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->dropForeign(['reserved_by_user_id']);
            $table->dropIndex('slots_space_status_idx');
            $table->dropIndex('slots_reserved_until_idx');
            $table->dropColumn(['reserved_by_user_id', 'reserved_until']);
        });

        DB::statement("ALTER TABLE parking_slots MODIFY COLUMN status ENUM(
            'available',
            'booked',
            'blocked',
            'maintenance'
        ) NOT NULL DEFAULT 'available'");
    }
};
