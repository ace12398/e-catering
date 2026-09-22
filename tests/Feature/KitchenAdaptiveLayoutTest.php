<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KitchenTask;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenAdaptiveLayoutTest extends TestCase
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

    protected function createKitchenFixtures(): array
    {
        $vendor = Vendor::create([
            'name' => 'Dapur Utama',
            'slug' => 'dapur-utama',
            'phone' => '08123456789',
            'rating' => 4.8,
            'is_active' => true,
        ]);

        $cat = Category::create([
            'name' => 'Paket Nasi',
            'slug' => 'paket-nasi',
            'is_active' => true,
            'icon' => '🍱',
        ]);

        $menu = Menu::create([
            'name' => 'Nasi Kotak Ayam',
            'slug' => 'nasi-kotak-ayam',
            'description' => 'Ayam bakar lezat',
            'price' => 30000,
            'category_id' => $cat->id,
            'vendor_id' => $vendor->id,
            'calories' => 500,
            'is_available' => true,
            'is_halal' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $customer->id,
            'subtotal' => 60000,
            'status' => 'sedang_diproses',
            'payment_status' => 'lunas',
            'delivery_address' => 'Jl. Kebon Jeruk No. 10',
            'grand_total' => 60000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'item_name' => 'Nasi Kotak Ayam',
            'quantity' => 2,
            'unit_price' => 30000,
            'subtotal' => 60000,
        ]);

        $task1 = KitchenTask::create([
            'order_id' => $order->id,
            'status' => 'waiting',
            'priority' => 'normal',
            'station' => 'Dapur Utama',
        ]);

        $task2 = KitchenTask::create([
            'order_id' => $order->id,
            'status' => 'cooking',
            'priority' => 'high',
            'station' => 'Stasiun 1',
        ]);

        return compact('vendor', 'cat', 'menu', 'customer', 'order', 'task1', 'task2');
    }

    public function test_kitchen_page_is_accessible_by_admin_user(): void
    {
        $admin = $this->createAdminUser();
        $this->createKitchenFixtures();

        $response = $this->actingAs($admin)->get('/kitchen');

        $response->assertStatus(200);
        $response->assertSee('Papan Antrean Dapur', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Manajemen Dapur', false);
    }

    public function test_kitchen_page_renders_kpi_cards_and_kanban_panels_with_drag_handles(): void
    {
        $admin = $this->createAdminUser();
        $this->createKitchenFixtures();

        $response = $this->actingAs($admin)->get('/kitchen');

        $response->assertStatus(200);
        // Drag handles
        $response->assertSee('kitchen-kpi-drag-handle');
        $response->assertSee('kitchen-panel-drag-handle');

        // KPI Wrappers
        $response->assertSee('data-kpi-wrapper="waiting"', false);
        $response->assertSee('data-kpi-wrapper="cooking"', false);
        $response->assertSee('data-kpi-wrapper="packing"', false);
        $response->assertSee('data-kpi-wrapper="done"', false);

        // Kanban Panel Wrappers
        $response->assertSee('data-panel-wrapper="k-list-waiting"', false);
        $response->assertSee('data-panel-wrapper="k-list-cooking"', false);
        $response->assertSee('data-panel-wrapper="k-list-packing"', false);
        $response->assertSee('data-panel-wrapper="k-list-done"', false);
    }

    public function test_user_can_save_custom_kitchen_kpi_and_panels_order_and_styles(): void
    {
        $admin = $this->createAdminUser();

        $customKpiOrder    = ['done', 'packing', 'cooking', 'waiting'];
        $customPanelsOrder = ['k-list-done', 'k-list-packing', 'k-list-cooking', 'k-list-waiting'];
        $customStyles      = [
            'done'           => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 12],
            'k-list-waiting' => ['shape' => 'rounded', 'width' => 360, 'height' => 400, 'border_radius' => 24],
        ];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'kitchen_kpi_order'    => $customKpiOrder,
                'kitchen_panels_order' => $customPanelsOrder,
                'component_styles'     => [
                    'kitchen' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customKpiOrder, $matrix['kitchen_kpi_order']);
        $this->assertEquals($customPanelsOrder, $matrix['kitchen_panels_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['kitchen']['done']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['kitchen']['done']['width']);
        $this->assertEquals('rounded', $matrix['component_styles']['kitchen']['k-list-waiting']['shape']);
        $this->assertEquals(360, $matrix['component_styles']['kitchen']['k-list-waiting']['width']);
    }

    public function test_saved_kitchen_order_and_styles_persist_when_loading_kitchen_page(): void
    {
        $admin = $this->createAdminUser();
        $this->createKitchenFixtures();

        $customKpiOrder    = ['done', 'packing', 'cooking', 'waiting'];
        $customPanelsOrder = ['k-list-done', 'k-list-packing', 'k-list-cooking', 'k-list-waiting'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['kitchen_kpi_order'] = $customKpiOrder;
        $matrix['kitchen_panels_order'] = $customPanelsOrder;
        $matrix['component_styles']['kitchen'] = [
            'done' => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 12],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($admin)->get('/kitchen');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify KPI order in DOM
        $posDone    = strpos($content, 'data-kpi-wrapper="done"');
        $posPacking = strpos($content, 'data-kpi-wrapper="packing"');
        $posCooking = strpos($content, 'data-kpi-wrapper="cooking"');
        $posWaiting = strpos($content, 'data-kpi-wrapper="waiting"');
        $this->assertTrue($posDone < $posPacking && $posPacking < $posCooking && $posCooking < $posWaiting, 'KPI cards must render in saved custom order');

        // Verify Kanban Panels order in DOM
        $posPnlDone    = strpos($content, 'data-panel-wrapper="k-list-done"');
        $posPnlPacking = strpos($content, 'data-panel-wrapper="k-list-packing"');
        $posPnlCooking = strpos($content, 'data-panel-wrapper="k-list-cooking"');
        $posPnlWaiting = strpos($content, 'data-panel-wrapper="k-list-waiting"');
        $this->assertTrue($posPnlDone < $posPnlPacking && $posPnlPacking < $posPnlCooking && $posPnlCooking < $posPnlWaiting, 'Kanban list panels must render in saved custom order');
    }

    public function test_kitchen_layout_user_isolation(): void
    {
        $adminA = $this->createAdminUser();
        $adminB = $this->createAdminUser();
        $this->createKitchenFixtures();

        // Admin A saves custom order
        $orderA = ['done', 'waiting', 'cooking', 'packing'];
        $this->actingAs($adminA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'kitchen_kpi_order' => $orderA,
                'component_styles' => [
                    'kitchen' => [
                        'done' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12],
                    ],
                ],
            ],
        ]);

        // Verify Admin B's layout matrix does not contain Admin A's changes
        $prefB = WorkspacePreference::where('user_id', $adminB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('kitchen_kpi_order', $matrixB);

        // Admin B loads kitchen page
        $responseB = $this->actingAs($adminB)->get('/kitchen');
        $responseB->assertStatus(200);
    }

    public function test_kitchen_layout_page_isolation_with_finance_dashboard_and_menus(): void
    {
        $admin = $this->createAdminUser();

        // Save Dashboard & Finance & Menus layout first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'menu_items_order'    => [10, 20, 30],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'menus'     => ['category_pills' => ['shape' => 'sharp', 'width' => 200, 'height' => 44, 'border_radius' => 0]],
            ],
        ];
        $pref->save();

        // Save Kitchen layout
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'kitchen_kpi_order' => ['done', 'packing', 'cooking', 'waiting'],
                'component_styles' => [
                    'kitchen' => [
                        'done' => ['shape' => 'pill', 'width' => 300, 'height' => 120, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Verify Dashboard, Finance, and Menus keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals([10, 20, 30], $matrix['menu_items_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('sharp', $matrix['component_styles']['menus']['category_pills']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['kitchen']['done']['shape']);
    }

    public function test_kitchen_layout_can_be_reset_to_default(): void
    {
        $admin = $this->createAdminUser();

        // Save custom kitchen layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'  => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'kitchen_kpi_order'    => ['done', 'waiting', 'cooking', 'packing'],
            'kitchen_panels_order' => ['k-list-done', 'k-list-waiting', 'k-list-cooking', 'k-list-packing'],
            'component_styles'     => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'kitchen'   => ['done' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
            ],
        ];
        $pref->save();

        // Reset only kitchen page
        $response = $this->actingAs($admin)->postJson(route('workspace.reset-layout'), [
            'page' => 'kitchen',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Kitchen specific keys reset to default
        $this->assertEquals(['waiting', 'cooking', 'packing', 'done'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'], $matrix['kitchen_panels_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_kitchen_order_is_sanitized_cleanly(): void
    {
        $admin = $this->createAdminUser();

        // Invalid: partial, duplicates, unknown component
        $incomingKpi    = ['done', 'UNKNOWN', 'done', 'cooking'];
        $incomingPanels = ['k-list-done', 'INVALID_PANEL', 'k-list-done'];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'kitchen_kpi_order'    => $incomingKpi,
                'kitchen_panels_order' => $incomingPanels,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized orders should contain unique valid keys filled with defaults
        $this->assertEquals(['done', 'cooking', 'waiting', 'packing'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['k-list-done', 'k-list-waiting', 'k-list-cooking', 'k-list-packing'], $matrix['kitchen_panels_order']);
    }

    public function test_kitchen_operational_status_update_workflow_remains_functional(): void
    {
        $admin = $this->createAdminUser();
        $fixtures = $this->createKitchenFixtures();

        // Advance task1 from 'waiting' to 'cooking'
        $response = $this->actingAs($admin)->patch(route('kitchen.update-status', $fixtures['task1']->id), [
            'status' => 'cooking',
        ]);

        $response->assertRedirect(route('kitchen.index'));
        $this->assertEquals('cooking', $fixtures['task1']->fresh()->status);
    }
}
