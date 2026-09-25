<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->constrained()
                ->cascadeOnDelete();

            // Which cancellation_settings row applied
            $table->foreignId('cancellation_setting_id')
                ->nullable()
                ->constrained('cancellation_settings')
                ->nullOnDelete();

            // Amounts
            $table->decimal('booking_amount', 10, 2)
                ->comment('Original total_amount at time of cancellation');

            $table->decimal('refund_amount', 10, 2)
                ->comment('Amount returned to user');

            $table->decimal('cancellation_fee', 10, 2)
                ->default(0)
                ->comment('booking_amount - refund_amount');

            $table->decimal('owner_earnings', 10, 2)
                ->default(0)
                ->comment('Owner share of cancellation_fee');

            $table->decimal('dev_earnings', 10, 2)
                ->default(0)
                ->comment('Developer share of cancellation_fee');

            // Gateway
            $table->string('gateway_refund_id')
                ->nullable()
                ->comment('Razorpay rfnd_xxx or Stripe re_xxx');

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending');

            $table->text('notes')->nullable();

            // Who triggered the cancellation
            $table->string('initiated_by')
                ->default('user')
                ->comment('user | owner | admin | system');

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
