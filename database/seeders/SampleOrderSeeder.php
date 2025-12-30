<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Costume;
use App\Models\DanceService;
use App\Models\MakeupService;
use Carbon\Carbon;

class SampleOrderSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Hapus sample orders lama jika ada
        Order::where('order_code', 'LIKE', 'ORD-' . date('Ymd') . '-%')->delete();
        
        // Pastikan ada user customer terlebih dahulu
        $customer1 = User::where('role', 'customer')->first();
        
        if (!$customer1) {
            // Buat customer baru jika belum ada
            $customer1 = User::create([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer'
            ]);
        }

        $customer2 = User::where('email', 'jane@example.com')->first();
        if (!$customer2) {
            $customer2 = User::create([
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer'
            ]);
        }

        // Ambil data untuk order
        $costumes = Costume::where('stock', '>', 0)->take(3)->get();
        $danceServices = DanceService::where('is_available', true)->take(2)->get();
        $makeupServices = MakeupService::where('is_available', true)->take(2)->get();

        // ORDER 1: Costume + Dance (PENDING - untuk test payment)
        $order1 = Order::create([
            'user_id' => $customer1->id,
            'order_code' => 'ORD-' . date('Ymd') . '-001',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(9),
            'total_price' => 0, // akan di-update
            'total_amount' => 0,
            'status' => 'pending',
            'return_status' => 'belum'
        ]);

        $totalPrice = 0;

        // Order Detail: 2 Costume
        if ($costumes->count() >= 2) {
            OrderDetail::create([
                'order_id' => $order1->id,
                'service_type' => 'kostum',
                'detail_id' => $costumes[0]->id,
                'quantity' => 2,
                'unit_price' => $costumes[0]->rental_price
            ]);
            $totalPrice += $costumes[0]->rental_price * 2;

            OrderDetail::create([
                'order_id' => $order1->id,
                'service_type' => 'kostum',
                'detail_id' => $costumes[1]->id,
                'quantity' => 1,
                'unit_price' => $costumes[1]->rental_price
            ]);
            $totalPrice += $costumes[1]->rental_price;
        }

        // Order Detail: 1 Dance Service
        if ($danceServices->count() > 0) {
            OrderDetail::create([
                'order_id' => $order1->id,
                'service_type' => 'tari',
                'detail_id' => $danceServices[0]->id,
                'quantity' => 1,
                'unit_price' => $danceServices[0]->price
            ]);
            $totalPrice += $danceServices[0]->price;
        }

        $order1->update(['total_price' => $totalPrice, 'total_amount' => $totalPrice]);

        // ORDER 2: Makeup + Costume (PENDING - untuk test payment)
        $order2 = Order::create([
            'user_id' => $customer2->id,
            'order_code' => 'ORD-' . date('Ymd') . '-002',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(12),
            'total_price' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'return_status' => 'belum'
        ]);

        $totalPrice2 = 0;

        // Order Detail: 1 Makeup Service
        if ($makeupServices->count() > 0) {
            OrderDetail::create([
                'order_id' => $order2->id,
                'service_type' => 'rias',
                'detail_id' => $makeupServices[0]->id,
                'quantity' => 1,
                'unit_price' => $makeupServices[0]->price
            ]);
            $totalPrice2 += $makeupServices[0]->price;
        }

        // Order Detail: 3 Costume
        if ($costumes->count() >= 3) {
            OrderDetail::create([
                'order_id' => $order2->id,
                'service_type' => 'kostum',
                'detail_id' => $costumes[2]->id,
                'quantity' => 3,
                'unit_price' => $costumes[2]->rental_price
            ]);
            $totalPrice2 += $costumes[2]->rental_price * 3;
        }

        $order2->update(['total_price' => $totalPrice2, 'total_amount' => $totalPrice2]);

        // ORDER 3: Dance + Makeup (PENDING - untuk test payment)
        $order3 = Order::create([
            'user_id' => $customer1->id,
            'order_code' => 'ORD-' . date('Ymd') . '-003',
            'start_date' => Carbon::now()->addDays(14),
            'end_date' => Carbon::now()->addDays(14),
            'total_price' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'return_status' => 'belum'
        ]);

        $totalPrice3 = 0;

        // Order Detail: 1 Dance Service
        if ($danceServices->count() >= 2) {
            OrderDetail::create([
                'order_id' => $order3->id,
                'service_type' => 'tari',
                'detail_id' => $danceServices[1]->id,
                'quantity' => 1,
                'unit_price' => $danceServices[1]->price
            ]);
            $totalPrice3 += $danceServices[1]->price;
        }

        // Order Detail: 2 Makeup Service
        if ($makeupServices->count() >= 2) {
            OrderDetail::create([
                'order_id' => $order3->id,
                'service_type' => 'rias',
                'detail_id' => $makeupServices[1]->id,
                'quantity' => 2,
                'unit_price' => $makeupServices[1]->price
            ]);
            $totalPrice3 += $makeupServices[1]->price * 2;
        }

        $order3->update(['total_price' => $totalPrice3, 'total_amount' => $totalPrice3]);

        // ORDER 4: Paket Lengkap - Costume + Dance + Makeup (PENDING - untuk test payment)
        $order4 = Order::create([
            'user_id' => $customer2->id,
            'order_code' => 'ORD-' . date('Ymd') . '-004',
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(22),
            'total_price' => 0,
            'total_amount' => 0,
            'status' => 'pending',
            'return_status' => 'belum'
        ]);

        $totalPrice4 = 0;

        // Order Detail: 5 Costume (acara besar)
        if ($costumes->count() > 0) {
            foreach ($costumes as $costume) {
                OrderDetail::create([
                    'order_id' => $order4->id,
                    'service_type' => 'kostum',
                    'detail_id' => $costume->id,
                    'quantity' => 2,
                    'unit_price' => $costume->rental_price
                ]);
                $totalPrice4 += $costume->rental_price * 2;
            }
        }

        // Order Detail: Dance Service
        if ($danceServices->count() > 0) {
            OrderDetail::create([
                'order_id' => $order4->id,
                'service_type' => 'tari',
                'detail_id' => $danceServices[0]->id,
                'quantity' => 1,
                'unit_price' => $danceServices[0]->price
            ]);
            $totalPrice4 += $danceServices[0]->price;
        }

        // Order Detail: Makeup Service
        if ($makeupServices->count() > 0) {
            OrderDetail::create([
                'order_id' => $order4->id,
                'service_type' => 'rias',
                'detail_id' => $makeupServices[0]->id,
                'quantity' => 3,
                'unit_price' => $makeupServices[0]->price
            ]);
            $totalPrice4 += $makeupServices[0]->price * 3;
        }

        $order4->update(['total_price' => $totalPrice4, 'total_amount' => $totalPrice4]);

        $this->command->info('✅ Sample orders created successfully!');
        $this->command->info('📦 Order 1: ' . $order1->order_code . ' - Rp ' . number_format($order1->total_price, 0, ',', '.'));
        $this->command->info('📦 Order 2: ' . $order2->order_code . ' - Rp ' . number_format($order2->total_price, 0, ',', '.'));
        $this->command->info('📦 Order 3: ' . $order3->order_code . ' - Rp ' . number_format($order3->total_price, 0, ',', '.'));
        $this->command->info('📦 Order 4: ' . $order4->order_code . ' - Rp ' . number_format($order4->total_price, 0, ',', '.'));
    }
}
