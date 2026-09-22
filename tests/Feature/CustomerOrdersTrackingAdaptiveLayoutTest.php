<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrdersTrackingAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(string $name = 'Customer User'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => 'customer',
            'username' => 'cust_' . uniqid(),
        ]);

        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );

        return $user;
    }

    protected function createSampleOrders(User $user): array
    {
        $vendor = Vendor::create([
            'name' => 'Catering Berkah ' . uniqid(),
            'slug' => 'catering-berkah-' . uniqid(),
            'address' => 'Jl. Kuliner No. 1',
            'phone' => '081234567890',
        ]);

        $category = Category::create([
            'name' => 'Paket Nasi Box ' . uniqid(),
            'slug' => 'nasi-box-' . uniqid(),
        ]);

        $menu1 = Menu::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Nasi Gudeg Komplit ' . uniqid(),
            'slug' => 'nasi-gudeg-' . uniqid(),
            'price' => 35000,
            'is_available' => true,
        ]);

        $menu2 = Menu::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Nasi Liwet Solo ' . uniqid(),
            'slug' => 'nasi-liwet-' . uniqid(),
            'price' => 30000,
            'is_available' => true,
        ]);

        // Order 1: pending
        $order1 = Order::create([
            'order_number'     => 'ORD-TEST-001',
            'user_id'          => $user->id,
            'subtotal'         => 70000,
            'tax'              => 7700,
            'delivery_fee'     => 10000,
            'grand_total'      => 87700,
            'status'           => 'menunggu_pembayaran',
            'payment_status'   => 'belum_dibayar',
            'payment_method'   => 'QRIS',
            'delivery_address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
        ]);

        OrderItem::create([
            'order_id'   => $order1->id,
            'menu_id'    => $menu1->id,
            'item_name'  => $menu1->name,
            'unit_price' => $menu1->price,
            'quantity'   => 2,
            'subtotal'   => 70000,
        ]);

        // Order 2: completed
        $order2 = Order::create([
            'order_number'     => 'ORD-TEST-002',
            'user_id'          => $user->id,
            'subtotal'         => 60000,
            'tax'              => 6600,
            'delivery_fee'     => 10000,
            'grand_total'      => 76600,
            'status'           => 'selesai',
            'payment_status'   => 'lunas',
            'payment_method'   => 'Transfer BCA',
            'delivery_address' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
        ]);

        OrderItem::create([
            'order_id'   => $order2->id,
            'menu_id'    => $menu2->id,
            'item_name'  => $menu2->name,
            'unit_price' => $menu2->price,
            'quantity'   => 2,
            'subtotal'   => 60000,
        ]);

        return [$order1, $order2];
    }

    public function test_customer_can_access_orders_history_workspace(): void
    {
        $customer = $this->createCustomer();
        $this->createSampleOrders($customer);

        $response = $this->actingAs($customer)->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Riwayat &amp; Pelacakan Pesanan', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Riwayat &amp; Pelacakan', false);
        $response->assertSee('⚙️ Sesuaikan Tampilan', false);
        $response->assertSee('orders-sections-container');
    }

    public function test_orders_renders_default_sections_and_kpi_order(): void
    {
        $customer = $this->createCustomer();
        $this->createSampleOrders($customer);

        $response = $this->actingAs($customer)->get('/orders');

        $response->assertStatus(200);

        // Sections
        $response->assertSee('data-orders-section="O-HEADER"', false);
        $response->assertSee('data-orders-section="O-KPI"', false);
        $response->assertSee('data-orders-section="O-FILTER"', false);
        $response->assertSee('data-orders-section="O-LIST"', false);
        $response->assertSee('data-orders-section="O-PAGINATION"', false);

        // KPI cards
        $response->assertSee('data-kpi-wrapper="pending"', false);
        $response->assertSee('data-kpi-wrapper="preparing"', false);
        $response->assertSee('data-kpi-wrapper="on_delivery"', false);
        $response->assertSee('data-kpi-wrapper="completed"', false);
    }

    public function test_customer_can_save_custom_orders_sections_kpis_and_styles(): void
    {
        $customer = $this->createCustomer();

        $customSectionOrder = ['O-KPI', 'O-HEADER', 'O-LIST', 'O-FILTER', 'O-PAGINATION'];
        $customKpiOrder = ['completed', 'on_delivery', 'preparing', 'pending'];
        $customStyles = [
            'O-HEADER'     => ['shape' => 'rounded', 'width' => 850, 'height' => 140, 'border_radius' => 24],
            'pending'      => ['shape' => 'pill', 'width' => 220, 'height' => 110, 'border_radius' => 9999],
            'completed'    => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 50],
            'O-LIST'       => ['shape' => 'sharp', 'width' => 880, 'height' => 500, 'border_radius' => 0],
        ];

        $response = $this->actingAs($customer)->postJson('/workspace/save-layout', [
            'layout_matrix' => [
                'orders_sections_order' => $customSectionOrder,
                'orders_kpi_order'      => $customKpiOrder,
                'component_styles'      => [
                    'orders' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $savedMatrix = $pref->effective_layout_matrix;

        $this->assertEquals($customSectionOrder, $savedMatrix['orders_sections_order']);
        $this->assertEquals($customKpiOrder, $savedMatrix['orders_kpi_order']);
        $this->assertEquals('rounded', $savedMatrix['component_styles']['orders']['O-HEADER']['shape']);
        $this->assertEquals('pill', $savedMatrix['component_styles']['orders']['pending']['shape']);
    }

    public function test_saved_orders_sections_and_kpis_order_persist_on_page_load(): void
    {
        $customer = $this->createCustomer();
        $this->createSampleOrders($customer);

        $customSectionOrder = ['O-KPI', 'O-LIST', 'O-HEADER', 'O-FILTER', 'O-PAGINATION'];

        $this->actingAs($customer)->postJson('/workspace/save-layout', [
            'layout_matrix' => [
                'orders_sections_order' => $customSectionOrder,
            ],
        ]);

        $customer->unsetRelation('preference');
        $response = $this->actingAs($customer)->get('/orders');

        $response->assertStatus(200);

        $content = $response->getContent();
        $posKpi    = strpos($content, 'data-orders-section="O-KPI"');
        $posList   = strpos($content, 'data-orders-section="O-LIST"');
        $posHeader = strpos($content, 'data-orders-section="O-HEADER"');

        $this->assertTrue($posKpi < $posList, 'O-KPI must precede O-LIST');
        $this->assertTrue($posList < $posHeader, 'O-LIST must precede O-HEADER');
    }

    public function test_customer_orders_layout_user_isolation(): void
    {
        $customerA = $this->createCustomer('Customer A');
        $customerB = $this->createCustomer('Customer B');

        $orderA = ['O-LIST', 'O-HEADER', 'O-KPI', 'O-FILTER', 'O-PAGINATION'];
        $orderB = ['O-KPI', 'O-FILTER', 'O-HEADER', 'O-LIST', 'O-PAGINATION'];

        $this->actingAs($customerA)->postJson('/workspace/save-layout', [
            'layout_matrix' => ['orders_sections_order' => $orderA],
        ]);

        $this->actingAs($customerB)->postJson('/workspace/save-layout', [
            'layout_matrix' => ['orders_sections_order' => $orderB],
        ]);

        $customerA->unsetRelation('preference');
        $customerB->unsetRelation('preference');

        $prefA = WorkspacePreference::where('user_id', $customerA->id)->first();
        $prefB = WorkspacePreference::where('user_id', $customerB->id)->first();

        $this->assertEquals($orderA, $prefA->effective_layout_matrix['orders_sections_order']);
        $this->assertEquals($orderB, $prefB->effective_layout_matrix['orders_sections_order']);
    }

    public function test_orders_layout_page_isolation(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->postJson('/workspace/save-layout', [
            'layout_matrix' => [
                'orders_sections_order' => ['O-LIST', 'O-KPI', 'O-HEADER', 'O-FILTER', 'O-PAGINATION'],
                'component_styles' => [
                    'orders' => [
                        'O-HEADER' => ['shape' => 'hexagon', 'width' => 800, 'height' => 150, 'border_radius' => 0],
                    ],
                ],
            ],
        ]);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $pref->effective_layout_matrix;

        // Verify other pages remained unchanged
        $this->assertEquals(['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'], $matrix['cart_sections_order']);
        $this->assertEquals(['M-HEADER', 'M-TOOLBAR', 'M-GRID'], $matrix['menu_sections_order']);
        $this->assertEquals('rectangle', $matrix['component_styles']['menus']['M-HEADER']['shape']);
        $this->assertEquals('rectangle', $matrix['component_styles']['cart']['C-CART-HEADER']['shape']);
    }

    public function test_orders_layout_can_be_reset_to_default(): void
    {
        $customer = $this->createCustomer();
        $this->createSampleOrders($customer);

        // Customize layout first
        $this->actingAs($customer)->postJson('/workspace/save-layout', [
            'layout_matrix' => [
                'orders_sections_order' => ['O-LIST', 'O-FILTER', 'O-KPI', 'O-HEADER', 'O-PAGINATION'],
                'component_styles' => [
                    'orders' => [
                        'O-HEADER' => ['shape' => 'pill', 'width' => 700, 'height' => 90, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Reset orders layout
        $response = $this->actingAs($customer)->postJson('/workspace/reset-layout', [
            'page' => 'orders',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $pref->effective_layout_matrix;

        $defaultMatrix = WorkspacePreference::getDefaultLayoutMatrix();
        $this->assertEquals($defaultMatrix['orders_sections_order'], $matrix['orders_sections_order']);
        $this->assertEquals('rectangle', $matrix['component_styles']['orders']['O-HEADER']['shape']);

        // Orders business records must remain unaffected
        $this->assertEquals(2, Order::where('user_id', $customer->id)->count());
    }

    public function test_orders_status_filter_remains_active_and_safe(): void
    {
        $customer = $this->createCustomer();
        $orders = $this->createSampleOrders($customer);

        // Filter: pending
        $response = $this->actingAs($customer)->get('/orders?status=pending');
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-001');
        $response->assertDontSee('ORD-TEST-002');

        // Filter: completed
        $responseCompleted = $this->actingAs($customer)->get('/orders?status=completed');
        $responseCompleted->assertStatus(200);
        $responseCompleted->assertSee('ORD-TEST-002');
        $responseCompleted->assertDontSee('ORD-TEST-001');
    }

    public function test_orders_search_functionality_remains_active_and_safe(): void
    {
        $customer = $this->createCustomer();
        $orders = $this->createSampleOrders($customer);

        // Search: ORD-TEST-001
        $response = $this->actingAs($customer)->get('/orders?search=ORD-TEST-001');
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-001');
        $response->assertDontSee('ORD-TEST-002');

        // Search by address: Sudirman
        $responseAddress = $this->actingAs($customer)->get('/orders?search=Sudirman');
        $responseAddress->assertStatus(200);
        $responseAddress->assertSee('ORD-TEST-001');
        $responseAddress->assertDontSee('ORD-TEST-002');
    }

    public function test_order_detail_navigation_and_tracking_view_remain_accessible(): void
    {
        $customer = $this->createCustomer();
        [$order1, $order2] = $this->createSampleOrders($customer);

        // Access order detail & tracking view
        $response = $this->actingAs($customer)->get("/orders/{$order1->id}");

        $response->assertStatus(200);
        $response->assertSee($order1->order_number);
        $response->assertSee('Progres Pesanan', false);
        $response->assertSee('Menunggu Pembayaran', false);
        $response->assertSee('Alamat Pengiriman', false);
        $response->assertSee('Jl. Sudirman No. 45, Jakarta Pusat');
        $response->assertSee('Bayar dengan QRIS');
    }
}
