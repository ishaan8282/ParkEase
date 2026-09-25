<?php

namespace Database\Seeders;

use App\Models\CancellationSetting;
use Illuminate\Database\Seeder;

class CancellationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'label' => 'Within 5 minutes',
                'window_from_minutes' => 0,
                'window_to_minutes' => 5,
                'refund_percentage' => 100,
                'owner_share_percentage' => 0,
                'dev_share_percentage' => 0,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'Within 5-30 minutes',
                'window_from_minutes' => 5,
                'window_to_minutes' => 30,
                'refund_percentage' => 75,
                'owner_share_percentage' => 20,
                'dev_share_percentage' => 5,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'label' => 'Within 30-60 minutes',
                'window_from_minutes' => 30,
                'window_to_minutes' => 60,
                'refund_percentage' => 50,
                'owner_share_percentage' => 40,
                'dev_share_percentage' => 10,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'label' => 'After 60 minutes',
                'window_from_minutes' => 60,
                'window_to_minutes' => null,
                'refund_percentage' => 0,
                'owner_share_percentage' => 80,
                'dev_share_percentage' => 20,
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            CancellationSetting::firstOrCreate(
                ['window_from_minutes' => $setting['window_from_minutes']],
                $setting
            );
        }
    }
}
