<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_settings', function (Blueprint $table) {
            $table->id();

            // The policy tier name shown in admin UI
            $table->string('label')->comment('e.g. "Within 5 minutes", "5–30 minutes"');

            // Time window: if cancelled within X minutes of BOOKING creation
            $table->unsignedInteger('window_from_minutes')
                ->default(0)
                ->comment('Window starts at N minutes after booking created');

            $table->unsignedInteger('window_to_minutes')
                ->nullable()
                ->comment('Window ends at N minutes. NULL = no upper limit (catch-all)');

            // Refund percentage to the user (0–100)
            $table->unsignedTinyInteger('refund_percentage')
                ->default(100)
                ->comment('% of total_amount returned to user');

            // Of the non-refunded amount, how much goes to owner (0–100)
            $table->unsignedTinyInteger('owner_share_percentage')
                ->default(60)
                ->comment('% of cancellation fee paid to owner');

            // The rest goes to developer (enforced in service: dev = 100 - owner_share)
            $table->unsignedTinyInteger('dev_share_percentage')
                ->default(40)
                ->comment('% of cancellation fee paid to developer');

            // Soft ordering for admin UI display
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the 3 default policy tiers described in the spec
        DB::table('cancellation_settings')->insert([
            [
                'label'                  => 'Within 5 minutes of booking',
                'window_from_minutes'    => 0,
                'window_to_minutes'      => 5,
                'refund_percentage'      => 100,
                'owner_share_percentage' => 0,
                'dev_share_percentage'   => 0,
                'sort_order'             => 1,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'label'                  => '5 to 30 minutes after booking',
                'window_from_minutes'    => 5,
                'window_to_minutes'      => 30,
                'refund_percentage'      => 50,
                'owner_share_percentage' => 60,
                'dev_share_percentage'   => 40,
                'sort_order'             => 2,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'label'                  => 'After 30 minutes (or near check-in)',
                'window_from_minutes'    => 30,
                'window_to_minutes'      => null,
                'refund_percentage'      => 0,
                'owner_share_percentage' => 60,
                'dev_share_percentage'   => 40,
                'sort_order'             => 3,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_settings');
    }
};
