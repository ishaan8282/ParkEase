<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parking_slot_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('booking_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // State transition
            $table->string('old_status', 20);
            $table->string('new_status', 20);

            // Who/what caused the change
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('trigger_source', 30)
                ->default('system')
                ->comment('system | user | admin | payment_webhook | scheduler');

            $table->text('notes')->nullable();

            // No updated_at — this table is append-only
            $table->timestamp('created_at')->useCurrent();

            $table->index(['parking_slot_id', 'created_at']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_status_logs');
    }
};
