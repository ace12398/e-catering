<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryAdaptiveLayoutTest extends TestCase
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

    protected function createDeliveryFixtures(): array
    {
        $vendor = Vendor::create([
            'name' => 'Dapur Katering Sejahtera',
            'slug' => 'dapur-katering-sejahtera',
            'phone' => '08123456789',
            'rating' => 4.9,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'name' => 'Prasmanan',
            'slug' => 'prasmanan',
            'is_active' => true,
            'icon' => '🍲',
        ]);

        $menu = Menu::create([
            'name' => 'Paket Prasmanan Mewah',
            'slug' => 'paket-prasmanan-mewah',
            'description' => 'Paket prasmanan lezat untuk acara',
            'price' => 75000,
            'category_id' => $cat->id,
            'vendor_id' => $vendor->id,
            'calories' => 700,
            'is_available' => true,
            'is_halal' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);
        $courier  = User::factory()->create(['role' => 'admin', 'name' => 'Pak Budi Kurir']);

        $order1 = Order::create([
            'order_number' => 'ORD-DEL-001',
            'user_id' => $customer->id,
            'subtotal' => 150000,
            'status' => 'sedang_dikirim',
            'payment_status' => 'lunas',
            'delivery_address' => 'Jl. Sudirman No. 12',
            'grand_total' => 150000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'menu_id' => $menu->id,
            'item_name' => 'Paket Prasmanan Mewah',
            'quantity' => 2,
            'unit_price' => 75000,
            'subtotal' => 150000,
        ]);

        $order2 = Order::create([
            'order_number' => 'ORD-DEL-002',
            'user_id' => $customer->id,
            'subtotal' => 75000,
            'status' => 'sedang_diproses',
            'payment_status' => 'lunas',
            'delivery_address' => 'Jl. Thamrin No. 45',
            'grand_total' => 75000,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_id' => $menu->id,
            'item_name' => 'Paket Prasmanan Mewah',
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
        ]);

        $del1 = Delivery::create([
            'order_id' => $order1->id,
            'courier_id' => $courier->id,
            'vehicle' => 'Motor',
            'status' => 'dalam_pengiriman',
            'recipient_name' => 'Ibu Siti',
            'recipient_phone' => '08129876543',
        ]);

        $del2 = Delivery::create([
            'order_id' => $order2->id,
            'courier_id' => null,
            'vehicle' => 'Mobil Box',
            'status' => 'waiting',
            'recipient_name' => 'Bpk. Hendra',
            'recipient_phone' => '08121112223',
        ]);

        return compact('vendor', 'cat', 'menu', 'customer', 'courier', 'order1', 'order2', 'del1', 'del2');
    }

    public function test_delivery_page_is_accessible_by_admin(): void
    {
        $admin = $this->createAdminUser();
        $this->createDeliveryFixtures();

        $response = $this->actingAs($admin)->get('/delivery');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengiriman', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Pengiriman & Kurir', false);
    }

    public function test_delivery_page_renders_kpi_cards_and_delivery_items_with_drag_handles(): void
    {
        $admin = $this->createAdminUser();
        $fixtures = $this->createDeliveryFixtures();

        $response = $this->actingAs($admin)->get('/delivery');

        $response->assertStatus(200);

        // Drag handles
        $response->assertSee('delivery-kpi-drag-handle');
        $response->assertSee('delivery-item-drag-handle');

        // KPI wrappers
        $response->assertSee('data-kpi-wrapper="waiting"', false);
        $response->assertSee('data-kpi-wrapper="on_delivery"', false);
        $response->assertSee('data-kpi-wrapper="done"', false);
        $response->assertSee('data-kpi-wrapper="total"', false);

        // Delivery Item wrappers
        $response->assertSee('data-item-wrapper="' . $fixtures['del1']->id . '"', false);
        $response->assertSee('data-item-wrapper="' . $fixtures['del2']->id . '"', false);
    }

    public function test_user_can_save_custom_delivery_kpi_and_items_order_and_styles(): void
    {
        $admin = $this->createAdminUser();
        $fixtures = $this->createDeliveryFixtures();

        $customKpiOrder   = ['done', 'total', 'on_delivery', 'waiting'];
        $customItemsOrder = [$fixtures['del2']->id, $fixtures['del1']->id];
        $customStyles     = [
            'done'                                    => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 12],
            'delivery_order_' . $fixtures['del1']->id => ['shape' => 'rounded', 'width' => 360, 'height' => 180, 'border_radius' => 24],
        ];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'delivery_kpi_order'   => $customKpiOrder,
                'delivery_items_order' => $customItemsOrder,
                'component_styles'     => [
                    'delivery' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customKpiOrder, $matrix['delivery_kpi_order']);
        $this->assertEquals($customItemsOrder, $matrix['delivery_items_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['delivery']['done']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['delivery']['done']['width']);
        $this->assertEquals('rounded', $matrix['component_styles']['delivery']['delivery_order_' . $fixtures['del1']->id]['shape']);
        $this->assertEquals(360, $matrix['component_styles']['delivery']['delivery_order_' . $fixtures['del1']->id]['width']);
    }

    public function test_saved_delivery_order_and_styles_persist_when_loading_delivery_page(): void
    {
        $admin = $this->createAdminUser();
        $fixtures = $this->createDeliveryFixtures();

        $customKpiOrder   = ['done', 'total', 'on_delivery', 'waiting'];
        $customItemsOrder = [$fixtures['del2']->id, $fixtures['del1']->id];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['delivery_kpi_order']   = $customKpiOrder;
        $matrix['delivery_items_order'] = $customItemsOrder;
        $matrix['component_styles']['delivery'] = [
            'done' => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 12],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($admin)->get('/delivery');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify KPI order in DOM
        $posDone       = strpos($content, 'data-kpi-wrapper="done"');
        $posTotal      = strpos($content, 'data-kpi-wrapper="total"');
        $posOnDelivery = strpos($content, 'data-kpi-wrapper="on_delivery"');
        $posWaiting    = strpos($content, 'data-kpi-wrapper="waiting"');
        $this->assertTrue($posDone < $posTotal && $posTotal < $posOnDelivery && $posOnDelivery < $posWaiting, 'KPI cards must render in saved custom order');

        // Verify Delivery Items order in DOM: del2 should appear before del1
        $posDel2 = strpos($content, 'data-item-wrapper="' . $fixtures['del2']->id . '"');
        $posDel1 = strpos($content, 'data-item-wrapper="' . $fixtures['del1']->id . '"');
        $this->assertTrue($posDel2 < $posDel1, 'Delivery cards must render in saved custom order');
    }

    public function test_delivery_layout_user_isolation(): void
    {
        $adminA = $this->createAdminUser();
        $adminB = $this->createAdminUser();
        $this->createDeliveryFixtures();

        // Admin A saves custom order
        $orderA = ['done', 'waiting', 'total', 'on_delivery'];
        $this->actingAs($adminA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'delivery_kpi_order' => $orderA,
                'component_styles' => [
                    'delivery' => [
                        'done' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12],
                    ],
                ],
            ],
        ]);

        // Verify Admin B's layout matrix does not contain Admin A's changes
        $prefB = WorkspacePreference::where('user_id', $adminB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('delivery_kpi_order', $matrixB);

        // Admin B loads delivery page
        $responseB = $this->actingAs($adminB)->get('/delivery');
        $responseB->assertStatus(200);
    }

    public function test_delivery_layout_page_isolation_with_finance_dashboard_menus_and_kitchen(): void
    {
        $admin = $this->createAdminUser();

        // Save Dashboard & Finance & Kitchen layout first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'   => ['done', 'packing', 'cooking', 'waiting'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'kitchen'   => ['done'     => ['shape' => 'pill', 'width' => 300, 'height' => 120, 'border_radius' => 9999]],
            ],
        ];
        $pref->save();

        // Save Delivery layout
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'delivery_kpi_order' => ['done', 'total', 'on_delivery', 'waiting'],
                'component_styles' => [
                    'delivery' => [
                        'done' => ['shape' => 'sharp', 'width' => 250, 'height' => 110, 'border_radius' => 0],
                    ],
                ],
            ],
        ]);

        // Verify Dashboard, Finance, and Kitchen keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['done', 'packing', 'cooking', 'waiting'], $matrix['kitchen_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['kitchen']['done']['shape']);
        $this->assertEquals('sharp', $matrix['component_styles']['delivery']['done']['shape']);
    }

    public function test_delivery_layout_can_be_reset_to_default(): void
    {
        $admin = $this->createAdminUser();

        // Save custom delivery layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'  => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'delivery_kpi_order'   => ['done', 'waiting', 'total', 'on_delivery'],
            'delivery_items_order' => [50, 40, 30],
            'component_styles'     => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'delivery'  => ['done' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
            ],
        ];
        $pref->save();

        // Reset only delivery page
        $response = $this->actingAs($admin)->postJson(route('workspace.reset-layout'), [
            'page' => 'delivery',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Delivery specific keys reset to default
        $this->assertEquals(['waiting', 'on_delivery', 'done', 'total'], $matrix['delivery_kpi_order']);
        $this->assertEquals([], $matrix['delivery_items_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_delivery_order_is_sanitized_cleanly(): void
    {
        $admin = $this->createAdminUser();

        // Invalid: partial, duplicates, unknown component
        $incomingKpi   = ['done', 'UNKNOWN_STAT', 'done', 'total'];
        $incomingItems = [10, -5, 'invalid_id', 20, 10];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'delivery_kpi_order'   => $incomingKpi,
                'delivery_items_order' => $incomingItems,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized orders should contain unique valid keys filled with defaults
        $this->assertEquals(['done', 'total', 'waiting', 'on_delivery'], $matrix['delivery_kpi_order']);
        $this->assertEquals([10, 20], $matrix['delivery_items_order']);
    }

    public function test_delivery_operational_status_update_workflow_remains_functional(): void
    {
        $admin = $this->createAdminUser();
        $fixtures = $this->createDeliveryFixtures();

        // Pickup delivery 2 (from waiting to dalam_pengiriman)
        $response = $this->actingAs($admin)->patch(route('delivery.update-status', $fixtures['del2']->id), [
            'action' => 'pickup',
        ]);

        $response->assertRedirect(route('delivery.index'));
        $this->assertEquals('dalam_pengiriman', $fixtures['del2']->fresh()->status);
    }
}
