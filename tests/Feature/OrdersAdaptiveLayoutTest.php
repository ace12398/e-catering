<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_' . uniqid(),
        ]);
        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );
        return $user;
    }

    protected function createCustomerUser(): User
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'username' => 'customer_' . uniqid(),
        ]);
        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );
        return $user;
    }

    public function test_orders_page_is_accessible_by_authenticated_user_and_admin(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Daftar Seluruh Pesanan', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Semua Pesanan', false);
    }

    public function test_orders_page_renders_kpi_cards_and_order_items_with_drag_handles(): void
    {
        $admin = $this->createAdminUser();

        // Create sample orders
        $order1 = Order::create([
            'order_number'     => 'ORD-' . uniqid(),
            'user_id'          => $admin->id,
            'subtotal'         => 100000,
            'grand_total'      => 100000,
            'status'           => 'menunggu_pembayaran',
            'payment_status'   => 'belum_dibayar',
            'delivery_address' => 'Jl. Kebon Jeruk No. 10',
        ]);
        $order2 = Order::create([
            'order_number'     => 'ORD-' . uniqid(),
            'user_id'          => $admin->id,
            'subtotal'         => 200000,
            'grand_total'      => 200000,
            'status'           => 'selesai',
            'payment_status'   => 'lunas',
            'delivery_address' => 'Jl. Thamrin No. 20',
        ]);

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertStatus(200);

        // Drag handles
        $response->assertSee('orders-kpi-drag-handle');
        $response->assertSee('orders-item-drag-handle');

        // KPI wrappers
        $response->assertSee('data-kpi-wrapper="pending"', false);
        $response->assertSee('data-kpi-wrapper="preparing"', false);
        $response->assertSee('data-kpi-wrapper="on_delivery"', false);
        $response->assertSee('data-kpi-wrapper="completed"', false);

        // Order wrappers
        $response->assertSee('data-order-wrapper="' . $order1->id . '"', false);
        $response->assertSee('data-order-wrapper="' . $order2->id . '"', false);
    }

    public function test_user_can_save_custom_orders_kpi_and_items_order_and_styles(): void
    {
        $admin = $this->createAdminUser();

        $customKpiOrder   = ['completed', 'on_delivery', 'preparing', 'pending'];
        $customItemsOrder = [99, 88, 77];
        $customStyles     = [
            'pending'   => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16],
            'completed' => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 16],
        ];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'orders_kpi_order'   => $customKpiOrder,
                'orders_items_order' => $customItemsOrder,
                'component_styles'   => [
                    'orders' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customKpiOrder, $matrix['orders_kpi_order']);
        $this->assertEquals($customItemsOrder, $matrix['orders_items_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['orders']['pending']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['orders']['pending']['width']);
        $this->assertEquals('circle', $matrix['component_styles']['orders']['completed']['shape']);
    }

    public function test_saved_orders_order_and_styles_persist_when_loading_orders_page(): void
    {
        $admin = $this->createAdminUser();

        $orderA = Order::create([
            'order_number'     => 'ORD-AAA-01',
            'user_id'          => $admin->id,
            'subtotal'         => 100000,
            'grand_total'      => 100000,
            'status'           => 'menunggu_pembayaran',
            'payment_status'   => 'belum_dibayar',
            'delivery_address' => 'Jl. Kebon Jeruk No. 10',
            'created_at'       => now()->subMinutes(10),
        ]);
        $orderB = Order::create([
            'order_number'     => 'ORD-BBB-02',
            'user_id'          => $admin->id,
            'subtotal'         => 200000,
            'grand_total'      => 200000,
            'status'           => 'selesai',
            'payment_status'   => 'lunas',
            'delivery_address' => 'Jl. Thamrin No. 20',
            'created_at'       => now()->subMinutes(5),
        ]);

        // User customizes order: orderA before orderB (reversing latest default)
        $customItemsOrder = [$orderA->id, $orderB->id];
        $customKpiOrder   = ['completed', 'on_delivery', 'preparing', 'pending'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['orders_kpi_order']   = $customKpiOrder;
        $matrix['orders_items_order'] = $customItemsOrder;
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify KPI order in DOM
        $posCompleted = strpos($content, 'data-kpi-wrapper="completed"');
        $posPending   = strpos($content, 'data-kpi-wrapper="pending"');
        $this->assertTrue($posCompleted < $posPending, 'KPI cards must render in saved custom order');

        // Verify Order items order in DOM
        $posOrderA = strpos($content, 'data-order-wrapper="' . $orderA->id . '"');
        $posOrderB = strpos($content, 'data-order-wrapper="' . $orderB->id . '"');
        $this->assertTrue($posOrderA < $posOrderB, 'Order items must render in saved custom order');
    }

    public function test_orders_layout_user_isolation(): void
    {
        $adminA = $this->createAdminUser();
        $adminB = $this->createAdminUser();

        // Admin A saves custom order
        $orderA = ['completed', 'on_delivery', 'preparing', 'pending'];
        $this->actingAs($adminA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'orders_kpi_order' => $orderA,
                'component_styles' => [
                    'orders' => [
                        'completed' => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Verify Admin B's layout matrix does not contain Admin A's changes
        $prefB = WorkspacePreference::where('user_id', $adminB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('orders_kpi_order', $matrixB);

        // Admin B loads orders page
        $responseB = $this->actingAs($adminB)->get('/orders');
        $responseB->assertStatus(200);
    }

    public function test_orders_layout_page_isolation_with_finance_dashboard_menus_kitchen_delivery_analytics(): void
    {
        $admin = $this->createAdminUser();

        // Save Finance, Dashboard, Menus, Kitchen, Delivery, Analytics first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'   => ['done', 'packing', 'cooking', 'waiting'],
            'delivery_kpi_order'  => ['done', 'total', 'on_delivery', 'waiting'],
            'analytics_kpi_order' => ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'kitchen'   => ['done'     => ['shape' => 'pill', 'width' => 300, 'height' => 120, 'border_radius' => 9999]],
                'delivery'  => ['done'     => ['shape' => 'sharp', 'width' => 250, 'height' => 110, 'border_radius' => 0]],
                'analytics' => ['A-KPI-06' => ['shape' => 'rounded', 'width' => 260, 'height' => 130, 'border_radius' => 24]],
            ],
        ];
        $pref->save();

        // Save Orders layout
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'orders_kpi_order' => ['on_delivery', 'pending', 'preparing', 'completed'],
                'component_styles' => [
                    'orders' => [
                        'pending' => ['shape' => 'rounded', 'width' => 260, 'height' => 130, 'border_radius' => 24],
                    ],
                ],
            ],
        ]);

        // Verify other pages' keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['done', 'packing', 'cooking', 'waiting'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['done', 'total', 'on_delivery', 'waiting'], $matrix['delivery_kpi_order']);
        $this->assertEquals(['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'], $matrix['analytics_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['kitchen']['done']['shape']);
        $this->assertEquals('sharp', $matrix['component_styles']['delivery']['done']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['analytics']['A-KPI-06']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['orders']['pending']['shape']);
    }

    public function test_orders_layout_can_be_reset_to_default(): void
    {
        $admin = $this->createAdminUser();

        // Save custom orders layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'orders_kpi_order'    => ['completed', 'on_delivery', 'preparing', 'pending'],
            'orders_items_order'  => [10, 20, 30],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'orders'    => ['completed' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
            ],
        ];
        $pref->save();

        // Reset only orders page
        $response = $this->actingAs($admin)->postJson(route('workspace.reset-layout'), [
            'page' => 'orders',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Orders specific keys reset to default
        $this->assertEquals(['pending', 'preparing', 'on_delivery', 'completed'], $matrix['orders_kpi_order']);
        $this->assertEquals([], $matrix['orders_items_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_orders_order_is_sanitized_cleanly(): void
    {
        $admin = $this->createAdminUser();

        // Invalid: partial, duplicates, unknown component
        $incomingKpi   = ['completed', 'UNKNOWN_STATUS', 'completed', 'pending'];
        $incomingItems = ['invalid', 50, -10, 50, 60];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'orders_kpi_order'   => $incomingKpi,
                'orders_items_order' => $incomingItems,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized orders should contain unique valid keys filled with defaults
        $this->assertEquals(['completed', 'pending', 'preparing', 'on_delivery'], $matrix['orders_kpi_order']);
        $this->assertEquals([50, 60], $matrix['orders_items_order']);
    }

    public function test_orders_business_data_and_details_workflow_remain_unaltered_and_functional(): void
    {
        $admin = $this->createAdminUser();

        $order = Order::create([
            'order_number'     => 'ORD-TEST-999',
            'user_id'          => $admin->id,
            'status'           => 'menunggu_pembayaran',
            'payment_status'   => 'belum_dibayar',
            'delivery_address' => 'Jl. Merdeka No. 45, Jakarta Pusat',
            'subtotal'         => 450000,
            'grand_total'      => 450000,
        ]);

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Jl. Merdeka No. 45');
        $response->assertSee(format_idr(450000));

        // Test accessing order show page
        $showResponse = $this->actingAs($admin)->get('/orders/' . $order->id);
        $showResponse->assertStatus(200);
    }
}
