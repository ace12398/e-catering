<?php
namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ActivityLog;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\KitchenTask;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed referential data first
        $this->call(CategorySeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(PaymentMethodSeeder::class);

        // 2. Seed Spatie Roles & Permissions if available
        if (class_exists(\Database\Seeders\PermissionSeeder::class)) {
            $this->call(PermissionSeeder::class);
        }
        if (class_exists(\Database\Seeders\RoleSeeder::class)) {
            $this->call(RoleSeeder::class);
        }

        // 3. Seed Admin Accounts
        $admins = $this->seedAdminAccounts();
        $mainCustomer = $this->seedCustomerAccount();

        // 4. Seed 24 Bulk Customers (Total 25 Customers)
        $bulkCustomers = $this->seedBulkCustomers();

        // 5. Seed 6 Couriers
        $couriers = $this->seedCouriers();

        // 6. Seed 45+ Orders, 110+ OrderItems, 40+ Kitchen Tasks, 35+ Deliveries, 45 Invoices
        $allCustomers = array_merge([$mainCustomer], $bulkCustomers);
        $this->seedOrdersAndOperations($allCustomers, $couriers);

        // 7. Seed 35+ Activity Logs
        $this->seedActivityLogs($allCustomers);

        $this->command->info('✅ Database seeding with complete Admin & Customer data finished successfully!');
    }

    // =========================================================
    // ACCOUNT SEEDING
    // =========================================================

    private function seedAdminAccounts(): array
    {
        $accounts = [
            ['username' => 'admin',          'name' => 'Admin Utama E-Catering',   'email' => 'admin@ecatering.co.id',          'role' => UserRole::ADMIN],
            ['username' => 'admin2',         'name' => 'Admin Sistem Operasional', 'email' => 'admin2@ecatering.co.id',         'role' => UserRole::ADMIN],
            ['username' => 'admin_ops',      'name' => 'Budi Santoso (Admin Operasional)', 'email' => 'admin_ops@ecatering.co.id', 'role' => UserRole::ADMIN],
            ['username' => 'admin_dapur',    'name' => 'Hendri Kurnia (Admin Dapur)',      'email' => 'admin_dapur@ecatering.co.id', 'role' => UserRole::ADMIN],
            ['username' => 'admin_keuangan', 'name' => 'Siska Permata (Admin Keuangan)',   'email' => 'admin_keuangan@ecatering.co.id', 'role' => UserRole::ADMIN],
        ];

        $users = [];
        foreach ($accounts as $acc) {
            $user = User::updateOrCreate(
                ['username' => $acc['username']],
                [
                    'name'     => $acc['name'],
                    'email'    => $acc['email'],
                    'password' => '12345678',
                    'role'     => $acc['role'],
                    'status'   => UserStatus::ACTIVE,
                ]
            );
            $user->workspacePreference()->updateOrCreate([], ['onboarding_complete' => true]);
            $user->profile()->updateOrCreate([], [
                'company_name'          => 'E-Catering Indonesia',
                'phone_number'          => '08123456789' . rand(0, 9),
                'address'               => 'Jl. Gatot Subroto No. 123, Jakarta Selatan',
                'completion_percentage' => 100,
            ]);
            $users[] = $user;
        }
        return $users;
    }

    private function seedCustomerAccount(): User
    {
        $customer = User::updateOrCreate(
            ['username' => 'pelanggan'],
            [
                'name'     => 'Siti Rahma',
                'email'    => 'pelanggan@ecatering.co.id',
                'password' => '12345678',
                'role'     => UserRole::CUSTOMER,
                'status'   => UserStatus::ACTIVE,
            ]
        );
        $customer->workspacePreference()->updateOrCreate([], [
            'onboarding_complete' => true,
            'preset'              => 'institution_organization',
            'density'             => 'comfortable',
        ]);
        $customer->profile()->updateOrCreate([], [
            'company_name'          => 'PT Rahma Jaya Abadi',
            'phone_number'          => '081298765432',
            'address'               => 'Gedung Wisma Asri Lt. 4, Jl. Jenderal Sudirman, Jakarta Barat',
            'completion_percentage' => 80,
        ]);
        return $customer;
    }

    private function seedBulkCustomers(): array
    {
        $customerData = [
            ['username' => 'dewi.anggraeni', 'name' => 'Dewi Anggraeni', 'company' => 'PT Maju Bersama', 'phone' => '081211110001', 'address' => 'Jl. Sudirman No. 45, Jakarta Pusat'],
            ['username' => 'andi.kusuma',    'name' => 'Andi Kusuma',    'company' => 'CV Karya Mandiri',  'phone' => '081211110002', 'address' => 'Jl. Thamrin No. 12, Jakarta Pusat'],
            ['username' => 'rina.sari',      'name' => 'Rina Sari',      'company' => 'Yayasan Bakti Sosial', 'phone' => '081211110003', 'address' => 'Jl. Hayam Wuruk No. 88, Jakarta Barat'],
            ['username' => 'fajar.hidayat',  'name' => 'Fajar Hidayat',  'company' => 'PT Sentosa Jaya',  'phone' => '081211110004', 'address' => 'Jl. Kebon Jeruk No. 22, Jakarta Barat'],
            ['username' => 'mega.putri',     'name' => 'Mega Putri',     'company' => 'PT Global Internasional', 'phone' => '081211110005', 'address' => 'Jl. Gajah Mada No. 10, Jakarta Barat'],
            ['username' => 'yusuf.rahmat',   'name' => 'Yusuf Rahmat',   'company' => 'PT Artha Mulia',   'phone' => '081211110006', 'address' => 'Jl. Gatot Subroto No. 77, Jakarta Selatan'],
            ['username' => 'linda.wulandari', 'name' => 'Linda Wulandari', 'company' => 'CV Sejahtera',   'phone' => '081211110007', 'address' => 'Jl. Rasuna Said No. 5, Jakarta Selatan'],
            ['username' => 'hendra.putra',   'name' => 'Hendra Putra',   'company' => 'PT Terang Benderang', 'phone' => '081211110008', 'address' => 'Jl. Kuningan No. 33, Jakarta Selatan'],
            ['username' => 'siska.amalia',   'name' => 'Siska Amalia',   'company' => 'PT Nusantara',      'phone' => '081211110009', 'address' => 'Jl. HR Rasuna Said No. 8, Jakarta Selatan'],
            ['username' => 'tommy.setiawan', 'name' => 'Tommy Setiawan', 'company' => 'PT Cakra Sakti',   'phone' => '081211110010', 'address' => 'Jl. Casablanca No. 19, Jakarta Selatan'],
            ['username' => 'nadia.fitriani',  'name' => 'Nadia Fitriani', 'company' => 'PT Digital Kreatif', 'phone' => '081211110011', 'address' => 'Jl. Kemang Raya No. 15, Jakarta Selatan'],
            ['username' => 'rizki.pratama',  'name' => 'Rizki Pratama',  'company' => 'PT Permata Group',  'phone' => '081211110012', 'address' => 'Jl. Wolter Monginsidi No. 4, Jakarta Selatan'],
            ['username' => 'dian.kusumawati', 'name' => 'Dian Kusumawati', 'company' => 'PT Indah Lestari', 'phone' => '081211110013', 'address' => 'Jl. Fatmawati No. 6, Jakarta Selatan'],
            ['username' => 'wahyu.nugroho',  'name' => 'Wahyu Nugroho',  'company' => 'PT Bumi Subur',    'phone' => '081211110014', 'address' => 'Jl. Cilandak No. 11, Jakarta Selatan'],
            ['username' => 'sri.handayani',  'name' => 'Sri Handayani',  'company' => 'CV Ananda',         'phone' => '081211110015', 'address' => 'Jl. Lebak Bulus No. 3, Jakarta Selatan'],
            ['username' => 'bima.arjuna',    'name' => 'Bima Arjuna',    'company' => 'PT Wijaya Kusuma',  'phone' => '081211110016', 'address' => 'Jl. Pondok Indah No. 24, Jakarta Selatan'],
            ['username' => 'ratna.dewi',     'name' => 'Ratna Dewi',     'company' => 'PT Surya Abadi',    'phone' => '081211110017', 'address' => 'Jl. Ampera No. 7, Jakarta Selatan'],
            ['username' => 'irfan.hakim',    'name' => 'Irfan Hakim',    'company' => 'PT Guna Karya',     'phone' => '081211110018', 'address' => 'Jl. Ragunan No. 9, Jakarta Selatan'],
            ['username' => 'ayu.cahyani',    'name' => 'Ayu Cahyani',    'company' => 'PT Muara Karya',    'phone' => '081211110019', 'address' => 'Jl. Pasar Minggu No. 14, Jakarta Selatan'],
            ['username' => 'joko.widodo',    'name' => 'Joko Widodo',    'company' => 'PT Rahayu Jaya',    'phone' => '081211110020', 'address' => 'Jl. Kalibata No. 2, Jakarta Selatan'],
            ['username' => 'bambang.sutrisno', 'name' => 'Bambang Sutrisno', 'company' => 'PT Graha Medika', 'phone' => '081211110021', 'address' => 'Jl. Daan Mogot No. 55, Jakarta Barat'],
            ['username' => 'anisa.rahmawati', 'name' => 'Anisa Rahmawati', 'company' => 'PT Cahaya Abadi', 'phone' => '081211110022', 'address' => 'Jl. Tomang Raya No. 18, Jakarta Barat'],
            ['username' => 'dono.prabowo',   'name' => 'Dono Prabowo',   'company' => 'PT Mitra Sejati',  'phone' => '081211110023', 'address' => 'Jl. Slipi No. 9, Jakarta Barat'],
            ['username' => 'erika.saputri',  'name' => 'Erika Saputri',  'company' => 'PT Prima Logistik', 'phone' => '081211110024', 'address' => 'Jl. Letjen S. Parman No. 30, Jakarta Barat'],
        ];

        $customers = [];
        foreach ($customerData as $data) {
            $user = User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'name'     => $data['name'],
                    'email'    => $data['username'] . '@mail.com',
                    'password' => '12345678',
                    'role'     => UserRole::CUSTOMER,
                    'status'   => UserStatus::ACTIVE,
                ]
            );
            $user->workspacePreference()->updateOrCreate([], ['onboarding_complete' => true]);
            $user->profile()->updateOrCreate([], [
                'company_name'          => $data['company'],
                'phone_number'          => $data['phone'],
                'address'               => $data['address'],
                'completion_percentage' => rand(70, 100),
            ]);
            $customers[] = $user;
        }

        return $customers;
    }

    private function seedCouriers(): array
    {
        $couriersData = [
            ['username' => 'kurir.ahmad', 'name' => 'Ahmad Subagyo (Kurir 01)', 'phone' => '081288880001'],
            ['username' => 'kurir.benny', 'name' => 'Benny Hartono (Kurir 02)', 'phone' => '081288880002'],
            ['username' => 'kurir.citra', 'name' => 'Citra Dewi (Kurir 03)',    'phone' => '081288880003'],
            ['username' => 'kurir.darma', 'name' => 'Darmawan (Kurir 04)',     'phone' => '081288880004'],
            ['username' => 'kurir.eko',   'name' => 'Eko Prasetyo (Kurir 05)', 'phone' => '081288880005'],
            ['username' => 'kurir.farhan', 'name' => 'Farhan Saputra (Kurir 06)', 'phone' => '081288880006'],
        ];

        $couriers = [];
        foreach ($couriersData as $cData) {
            $user = User::updateOrCreate(
                ['username' => $cData['username']],
                [
                    'name'     => $cData['name'],
                    'email'    => $cData['username'] . '@ecatering.co.id',
                    'password' => '12345678',
                    'role'     => UserRole::ADMIN,
                    'status'   => UserStatus::ACTIVE,
                ]
            );
            $user->profile()->updateOrCreate([], [
                'phone_number' => $cData['phone'],
                'address'      => 'Armada Pengiriman E-Catering Jakarta',
            ]);
            $couriers[] = $user;
        }

        return $couriers;
    }

    // =========================================================
    // ORDERS & OPERATIONS SEEDING
    // =========================================================

    private function seedOrdersAndOperations(array $customers, array $couriers): void
    {
        $menus = Menu::all();
        if ($menus->isEmpty()) return;

        $menuList = $menus->values()->all();
        $orderStatuses = ['menunggu_pembayaran', 'menunggu_verifikasi', 'sedang_diproses', 'sedang_dimasak', 'sedang_dikirim', 'selesai', 'selesai', 'selesai'];
        $kitchenStatuses = ['waiting', 'cooking', 'packing', 'done'];
        $deliveryStatuses = ['waiting', 'siap_diambil', 'dalam_pengiriman', 'selesai'];
        $invoiceStatuses = ['lunas', 'belum_dibayar', 'menunggu_verifikasi'];

        $orderCounter = 1;

        foreach ($customers as $index => $customer) {
            // Seed 2 orders per customer -> Total 50 orders
            for ($o = 1; $o <= 2; $o++) {
                $orderNumber = 'ORD-2026' . date('m') . '-' . str_pad($orderCounter++, 4, '0', STR_PAD_LEFT);
                $daysAgo = rand(0, 30);
                $status = $orderStatuses[array_rand($orderStatuses)];

                // Payment status sesuai order status (menggunakan nilai string baru)
                $paymentStatus = match($status) {
                    'selesai', 'sedang_dikirim', 'sedang_dimasak', 'sedang_diproses' => 'lunas',
                    'menunggu_verifikasi' => 'menunggu_verifikasi',
                    default => 'belum_dibayar',
                };

                $numItems = rand(2, 4);
                $items = [];
                for ($k = 0; $k < $numItems; $k++) {
                    $menu = $menuList[array_rand($menuList)];
                    $qty = rand(15, 60);
                    $items[] = [$menu, $qty, $menu->price];
                }

                $order = $this->createOrderRecord($customer, $orderNumber, $items, $status, $paymentStatus, $daysAgo);

                if ($order) {
                    // Kitchen Task Status Mapping
                    $kStatus = match($status) {
                        'menunggu_pembayaran', 'menunggu_verifikasi' => 'waiting',
                        'sedang_diproses'    => 'cooking',
                        'sedang_dimasak'     => $kitchenStatuses[array_rand([1, 2])], // cooking or packing
                        'sedang_dikirim', 'selesai' => 'done',
                        default => 'waiting',
                    };
                    $this->createKitchenTaskRecord($order, $kStatus, -$daysAgo);

                    // Delivery Status Mapping
                    if (in_array($status, ['sedang_diproses', 'sedang_dimasak', 'sedang_dikirim', 'selesai'])) {
                        $dStatus = match($status) {
                            'sedang_diproses' => 'siap_diambil',
                            'sedang_dimasak'  => 'siap_diambil',
                            'sedang_dikirim'  => 'dalam_pengiriman',
                            'selesai'         => 'selesai',
                            default           => 'waiting',
                        };
                        $assignedCourier = $couriers[array_rand($couriers)];
                        $this->createDeliveryRecord($order, $dStatus, $assignedCourier, -$daysAgo);
                    } else {
                        $this->createDeliveryRecord($order, 'waiting', $couriers[0], -$daysAgo);
                    }

                    // Invoice Status Mapping
                    $invStatus = match($paymentStatus) {
                        'lunas'               => 'lunas',
                        'menunggu_verifikasi' => 'menunggu_verifikasi',
                        default               => 'belum_dibayar',
                    };
                    $this->createInvoiceRecord($order, $invStatus, -$daysAgo);
                }
            }
        }
    }

    private function createOrderRecord(User $customer, string $orderNumber, array $items, string $status, string $paymentStatus, int $daysAgo): ?Order
    {
        try {
            $subtotal = 0;
            foreach ($items as [$menu, $qty, $price]) {
                if ($menu) $subtotal += $qty * $price;
            }
            $tax = round($subtotal * 0.11);
            $grandTotal = $subtotal + $tax;

            $createdAt = now()->subDays($daysAgo)->setTime(rand(8, 17), rand(0, 59));

            $order = Order::create([
                'order_number'     => $orderNumber,
                'user_id'          => $customer->id,
                'subtotal'         => $subtotal,
                'tax'              => $tax,
                'discount'         => 0,
                'grand_total'      => $grandTotal,
                'status'           => $status,
                'payment_status'   => $paymentStatus,
                'delivery_address' => $customer->profile?->address ?? 'Jakarta Selatan',
                'notes'            => 'Mohon disajikan hangat saat pengiriman.',
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            foreach ($items as [$menu, $qty, $price]) {
                if ($menu) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'menu_id'    => $menu->id,
                        'item_name'  => $menu->name,
                        'quantity'   => $qty,
                        'unit_price' => $price,
                        'subtotal'   => $qty * $price,
                    ]);
                }
            }
            return $order;
        } catch (\Throwable $e) {
            Log::error('Seeder createOrderRecord error: ' . $e->getMessage());
            return null;
        }
    }

    private function createKitchenTaskRecord(Order $order, string $status, int $daysAgo): void
    {
        try {
            $stations = ['Dapur Utama (Makanan Utama)', 'Dapur Bantu (Lauk Pauk)', 'Stasiun Pengemasan Bento', 'Stasiun Snack & Minuman'];
            $priorities = ['normal', 'high', 'rush'];
            $createdAt = now()->addDays($daysAgo)->setTime(rand(7, 11), rand(0, 59));

            KitchenTask::create([
                'uuid'             => Str::uuid(),
                'order_id'         => $order->id,
                'assigned_chef_id' => null,
                'station'          => $stations[array_rand($stations)],
                'priority'         => $priorities[array_rand($priorities)],
                'status'           => $status,
                'started_at'       => in_array($status, ['cooking', 'packaging', 'done']) ? $createdAt : null,
                'finished_at'      => $status === 'done' ? $createdAt->copy()->addHours(rand(1, 3)) : null,
                'notes'            => 'Porsi sesuai standar ISO katering',
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
        } catch (\Throwable $e) {
            Log::error('Seeder createKitchenTaskRecord error: ' . $e->getMessage());
        }
    }

    private function createDeliveryRecord(Order $order, string $status, User $courier, int $daysAgo): void
    {
        try {
            $vehicles = ['Motor Box Katering', 'Mobil Van Pendingin', 'Pickup Box Kurir'];
            $createdAt = now()->addDays($daysAgo)->setTime(rand(10, 16), rand(0, 59));

            Delivery::create([
                'uuid'            => Str::uuid(),
                'order_id'        => $order->id,
                'courier_id'      => $courier->id,
                'vehicle'         => $vehicles[array_rand($vehicles)],
                'status'          => $status,
                'priority'        => 'normal',
                'pickup_time'     => $createdAt,
                'delivered_time'  => $status === 'delivered' ? $createdAt->copy()->addHours(rand(1, 2)) : null,
                'recipient_name'  => $order->user?->name ?? 'Penerima Katering',
                'recipient_phone' => $order->user?->profile?->phone_number ?? '081298765432',
                'notes'           => 'Tiba tepat waktu sebelum acara dimulai',
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);
        } catch (\Throwable $e) {
            Log::error('Seeder createDeliveryRecord error: ' . $e->getMessage());
        }
    }

    private function createInvoiceRecord(Order $order, string $status, int $daysAgo): void
    {
        try {
            if (Invoice::where('order_id', $order->id)->exists()) return;

            $invNumber = 'INV-' . now()->addDays($daysAgo)->format('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $createdAt = now()->addDays($daysAgo);

            Invoice::create([
                'uuid'           => Str::uuid(),
                'invoice_number' => $invNumber,
                'order_id'       => $order->id,
                'customer_id'    => $order->user_id,
                'subtotal'       => $order->subtotal,
                'discount'       => $order->discount,
                'tax'            => $order->tax,
                'grand_total'    => $order->grand_total,
                'status'         => $status,
                'due_date'       => $createdAt->copy()->addDays(7),
                'paid_at'        => $status === 'paid' ? $createdAt : null,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);
        } catch (\Throwable $e) {
            Log::error('Seeder createInvoiceRecord error: ' . $e->getMessage());
        }
    }

    private function seedActivityLogs(array $customers): void
    {
        $activities = [
            'Pesanan katering baru berhasil dibuat oleh pelanggan.',
            'Pembayaran tagihan invoice dikonfirmasi lunas.',
            'Tim Dapur Utama mulai memproses hidangan pesanan.',
            'Stasiun pengemasan menyelesaikan kemasan bento box.',
            'Kurir mengambil paket katering dan siap berangkat.',
            'Kurir tiba di lokasi pengiriman pelanggan.',
            'Pesanan katering berhasil diserahkan dan selesai.',
            'Perubahan preferensi tampilan antarmuka (Workspace Density).'
        ];

        foreach ($customers as $c) {
            for ($l = 0; $l < 2; $l++) {
                ActivityLog::create([
                    'user_id'      => $c->id,
                    'action'       => $activities[array_rand($activities)],
                    'entity_type'  => 'order_system',
                    'performed_at' => now()->subHours(rand(1, 120)),
                ]);
            }
        }
    }
}
