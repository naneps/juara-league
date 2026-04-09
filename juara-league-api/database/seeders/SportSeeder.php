<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    /**
     * Data cabang olahraga dan esport yang relevan untuk platform kompetisi Indonesia.
     */
    private array $sports = [
        // ============================================================
        // OLAHRAGA FISIK (sport)
        // ============================================================
        [
            'name'     => 'Sepak Bola',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/26bd.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Futsal',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/26bd.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Basket',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3c0.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Bola Voli',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3d0.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Badminton',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3f8.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Tenis Meja',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3d3.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Tenis Lapangan',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3be.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Renang',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3ca.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Atletik',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3c3.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Pencak Silat',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f94a.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Taekwondo',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f94b.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Karate',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f94b.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Judo',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f94b.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Panahan',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3f9.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Bulu Tangkis Beregu',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3f8.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Golf',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/26f3.svg',
            'is_active' => false, // Tidak umum di turnamen lokal
        ],
        [
            'name'     => 'Rugby',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3c9.svg',
            'is_active' => false,
        ],
        [
            'name'     => 'Hoki',
            'type'     => 'sport',
            'icon_url' => 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14/assets/svg/1f3d1.svg',
            'is_active' => false,
        ],

        // ============================================================
        // ESPORT
        // ============================================================
        [
            'name'     => 'Mobile Legends: Bang Bang',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/id/2/2a/Mobile_Legends_Bang_Bang.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Free Fire',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/Free_Fire.png/220px-Free_Fire.png',
            'is_active' => true,
        ],
        [
            'name'     => 'PUBG Mobile',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/7/7b/PUBG_Mobile_logo.png',
            'is_active' => true,
        ],
        [
            'name'     => 'VALORANT',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/f/fc/Valorant_logo_-_pink_color_background.png',
            'is_active' => true,
        ],
        [
            'name'     => 'League of Legends',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/d/d8/League_of_Legends_2019_vector.svg',
            'is_active' => true,
        ],
        [
            'name'     => 'Dota 2',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/0/05/Dota_2.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Counter-Strike 2',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/CS2_Logo.svg/320px-CS2_Logo.svg.png',
            'is_active' => true,
        ],
        [
            'name'     => 'eFootball',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/f/f6/EFootball_logo.jpg',
            'is_active' => true,
        ],
        [
            'name'     => 'FIFA Online 4',
            'type'     => 'esport',
            'icon_url' => null,
            'is_active' => true,
        ],
        [
            'name'     => 'Street Fighter 6',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/c/ce/Street_Fighter_6_logo.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Tekken 8',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/1/1d/Tekken_8_box_art.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Clash of Clans',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/0/02/Clash_of_Clans_Logo.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Clash Royale',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/8/8c/Clash_Royale_Logo.png',
            'is_active' => true,
        ],
        [
            'name'     => 'Honor of Kings',
            'type'     => 'esport',
            'icon_url' => null,
            'is_active' => true,
        ],
        [
            'name'     => 'Genshin Impact',
            'type'     => 'esport',
            'icon_url' => 'https://upload.wikimedia.org/wikipedia/en/a/a2/Genshin_Impact_Logo.png',
            'is_active' => false,
        ],
        [
            'name'     => 'Apex Legends Mobile',
            'type'     => 'esport',
            'icon_url' => null,
            'is_active' => false,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏟️  Seeding sports data...');

        $insertedCount = 0;
        $skippedCount = 0;

        foreach ($this->sports as $sport) {
            $exists = DB::table('sports')->where('name', $sport['name'])->exists();

            if ($exists) {
                $this->command->warn("  ⚠  Skipped: {$sport['name']} (already exists)");
                $skippedCount++;
                continue;
            }

            DB::table('sports')->insert([
                'id'         => (string) Str::ulid(),
                'name'       => $sport['name'],
                'type'       => $sport['type'],
                'icon_url'   => $sport['icon_url'],
                'is_active'  => $sport['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = $sport['is_active'] ? '✓' : '○';
            $this->command->line("  {$status}  [{$sport['type']}] {$sport['name']}");
            $insertedCount++;
        }

        $this->command->newLine();
        $this->command->info("  ✅ Done! {$insertedCount} sports inserted, {$skippedCount} skipped.");
    }
}
