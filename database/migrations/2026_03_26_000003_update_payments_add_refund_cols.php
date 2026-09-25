<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Razorpay / Stripe refund ID returned by the gateway
            $table->string('refund_id')
                ->nullable()
                ->unique()
                ->after('order_id')
                ->comment('Gateway-side refund ID e.g. rfnd_xxxxx');

            $table->decimal('refund_amount', 10, 2)
                ->nullable()
                ->after('refund_id');

            $table->timestamp('refunded_at')
                ->nullable()
                ->after('refund_amount');

            $table->index('status', 'payments_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_idx');
            $table->dropUnique(['refund_id']);
            $table->dropColumn(['refund_id', 'refund_amount', 'refunded_at']);
        });
    }
};
