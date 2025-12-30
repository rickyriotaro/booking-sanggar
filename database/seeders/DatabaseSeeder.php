<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin RANTS',
            'email' => 'admin@rants.com',
            'phone_number' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Create Sample Customer
        User::create([
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'phone_number' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'customer'
        ]);

        // Seed Costumes
        $costumes = [
            [
                'costume_name' => 'Tari Melayu Kuning',
                'description' => 'Kostum tari melayu warna kuning dengan hiasan emas',
                'rental_price' => 150000,
                'stock' => 10,
                'size' => 'All Size'
            ],
            [
                'costume_name' => 'Tari Melayu Merah',
                'description' => 'Kostum tari melayu warna merah dengan hiasan emas',
                'rental_price' => 150000,
                'stock' => 8,
                'size' => 'All Size'
            ],
            [
                'costume_name' => 'Tari Zapin',
                'description' => 'Kostum lengkap untuk tari zapin',
                'rental_price' => 175000,
                'stock' => 12,
                'size' => 'M, L'
            ],
            [
                'costume_name' => 'Tari Saman',
                'description' => 'Kostum tari saman lengkap dengan aksesori',
                'rental_price' => 200000,
                'stock' => 15,
                'size' => 'All Size'
            ],
            [
                'costume_name' => 'Baju Adat Melayu',
                'description' => 'Baju adat melayu untuk acara resmi',
                'rental_price' => 250000,
                'stock' => 5,
                'size' => 'S, M, L, XL'
            ],
        ];

        foreach ($costumes as $costume) {
            Costume::create($costume);
        }

        // Seed Dance Services
        $danceServices = [
            [
                'package_name' => 'Paket Tari Tradisional 3 Penari',
                'dance_type' => 'Tradisional',
                'number_of_dancers' => 3,
                'price' => 500000,
                'duration_minutes' => 30,
                'description' => 'Paket 3 penari tarian tradisional - Cocok untuk acara kecil',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Tari Modern 5 Penari',
                'dance_type' => 'Modern',
                'number_of_dancers' => 5,
                'price' => 800000,
                'duration_minutes' => 45,
                'description' => 'Paket 5 penari tarian modern - Standar untuk acara sedang',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Tari Kontemporer 7 Penari',
                'dance_type' => 'Kontemporer',
                'number_of_dancers' => 7,
                'price' => 1200000,
                'duration_minutes' => 60,
                'description' => 'Paket 7 penari tarian kontemporer - Untuk acara besar',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Tari Kreasi Baru 9 Penari',
                'dance_type' => 'Kreasi Baru',
                'number_of_dancers' => 9,
                'price' => 1500000,
                'duration_minutes' => 75,
                'description' => 'Paket 9 penari kreasi baru - Untuk acara spesial',
                'is_available' => true
            ],
        ];

        foreach ($danceServices as $service) {
            DanceService::create($service);
        }

        // Seed Makeup Services
        $makeupServices = [
            [
                'package_name' => 'Paket Makeup SD',
                'category' => 'SD',
                'price' => 75000,
                'description' => 'Makeup untuk anak SD - Natural & Fresh',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Makeup SMP',
                'category' => 'SMP',
                'price' => 100000,
                'description' => 'Makeup untuk anak SMP - Natural dengan sentuhan glam',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Makeup SMA',
                'category' => 'SMA',
                'price' => 125000,
                'description' => 'Makeup untuk anak SMA - Fresh & Glowing',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Makeup Wisuda',
                'category' => 'Wisuda',
                'price' => 150000,
                'description' => 'Makeup untuk acara wisuda - Elegant & Flawless',
                'is_available' => true
            ],
            [
                'package_name' => 'Paket Makeup Acara Umum',
                'category' => 'Acara Umum',
                'price' => 200000,
                'description' => 'Makeup untuk acara umum/resmi - Full Glam',
                'is_available' => true
            ],
        ];

        foreach ($makeupServices as $service) {
            MakeupService::create($service);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@rants.com / password');
        $this->command->info('Customer Login: customer@test.com / password');
    }
}
