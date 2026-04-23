<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Identity
            [
                'key' => 'platform_name',
                'value' => 'Juara League',
                'group' => 'identity',
                'type' => 'string'
            ],
            [
                'key' => 'platform_tagline',
                'value' => 'Platform Turnamen Terlengkap di Indonesia',
                'group' => 'identity',
                'type' => 'string'
            ],
            
            // Platform Control
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'group' => 'system',
                'type' => 'boolean'
            ],
            [
                'key' => 'registration_enabled',
                'value' => 'true',
                'group' => 'system',
                'type' => 'boolean'
            ],

            // Social & Contact
            [
                'key' => 'contact_email',
                'value' => 'support@juara.league',
                'group' => 'contact',
                'type' => 'string'
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'instagram' => 'https://instagram.com/juaraleague',
                    'twitter' => 'https://twitter.com/juaraleague',
                    'discord' => 'https://discord.gg/juaraleague'
                ]),
                'group' => 'contact',
                'type' => 'json'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
