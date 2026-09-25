<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Distinguish walk-in (owner-registered) bookings from online ones
            $table->boolean('is_walk_in')->default(false)->after('status');

            // Walk-in customers may not have a registered account
            $table->string('customer_name')->nullable()->after('is_walk_in');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');

            // Payment tracking for walk-in (online bookings use the payments table)
            $table->enum('payment_status', ['pending', 'paid', 'cash', 'waived'])
                ->default('pending')
                ->after('customer_email');
            $table->timestamp('paid_at')->nullable()->after('payment_status');

            // Path to the generated PDF payslip
            $table->string('payslip_path')->nullable()->after('paid_at');

            // Free-form notes (e.g. "paid by cash at gate", "discount applied")
            $table->text('notes')->nullable()->after('payslip_path');

            // Indexes for common owner queries
            $table->index(['is_walk_in', 'created_at']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['is_walk_in', 'created_at']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'is_walk_in',
                'customer_name',
                'customer_phone',
                'customer_email',
                'payment_status',
                'paid_at',
                'payslip_path',
                'notes',
            ]);
        });
    }
};