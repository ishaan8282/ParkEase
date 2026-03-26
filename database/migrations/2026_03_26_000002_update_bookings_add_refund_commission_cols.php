<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Refund tracking
            $table->decimal('refund_amount', 10, 2)
                ->nullable()
                ->after('total_amount')
                ->comment('Amount actually refunded to user');

            $table->timestamp('refunded_at')
                ->nullable()
                ->after('refund_amount');

            // Commission split — stored on booking so reports never
            // require re-calculation even if settings change later
            $table->decimal('owner_commission', 10, 2)
                ->default(0)
                ->after('refunded_at')
                ->comment('Owner earnings after platform fee');

            $table->decimal('dev_commission', 10, 2)
                ->default(0)
                ->after('owner_commission')
                ->comment('Developer cut from platform fee');

            // Cancellation tracking
            $table->string('cancelled_by')->nullable()->after('cancellation_reason')
                ->comment('user | owner | admin | system');

            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');

            $table->index('status');
            $table->index('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['cancelled_at']);
            $table->dropColumn([
                'refund_amount',
                'refunded_at',
                'owner_commission',
                'dev_commission',
                'cancelled_by',
                'cancelled_at',
            ]);
        });
    }
};
