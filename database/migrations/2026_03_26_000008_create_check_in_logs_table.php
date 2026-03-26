<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('event_type', ['check_in', 'check_out'])
                ->comment('Whether this log is an arrival or departure scan');

            // QR scan or manual override
            $table->enum('method', ['qr_scan', 'manual'])
                ->default('qr_scan');

            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin/owner who performed manual override. NULL for QR self-scan.');

            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();

            // Was this scan valid?
            $table->boolean('is_successful')->default(true);
            $table->string('failure_reason')->nullable()
                ->comment('e.g. booking_expired, wrong_slot, already_checked_in');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['booking_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_logs');
    }
};
