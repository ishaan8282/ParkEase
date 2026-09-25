<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->unique() // one commission record per booking
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Gross amounts
            $table->decimal('booking_amount', 10, 2)
                ->comment('booking.amount before platform_fee');

            $table->decimal('platform_fee', 8, 2)
                ->comment('Total platform fee charged to user');

            // Split
            $table->decimal('owner_amount', 10, 2)
                ->comment('What the owner earns from this booking');

            $table->decimal('dev_amount', 8, 2)
                ->comment('Developer earnings from platform_fee');

            // Rates snapshot (so future setting changes don't alter history)
            $table->unsignedTinyInteger('owner_rate_pct')
                ->comment('Owner % at time of booking');

            $table->unsignedTinyInteger('dev_rate_pct')
                ->comment('Dev % at time of booking');

            // Payout tracking
            $table->enum('owner_payout_status', ['pending', 'processing', 'paid', 'failed'])
                ->default('pending');

            $table->timestamp('owner_paid_at')->nullable();

            $table->string('owner_payout_ref')->nullable()
                ->comment('Bank transfer / UPI ref for owner payout');

            $table->timestamps();

            $table->index(['owner_id', 'owner_payout_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
