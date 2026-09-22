<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Profile;
use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomerUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'customer',
            'username' => 'cust_' . uniqid(),
        ], $attributes));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'PT Mandiri Jaya',
                'phone_number' => '08123456789',
                'address'      => 'Jl. Sudirman Kav 50, Jakarta',
            ]
        );

        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );

        return $user;
    }

    protected function createAdminUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'admin',
            'username' => 'admin_' . uniqid(),
        ], $attributes));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'CaterFlow HQ',
                'phone_number' => '08199998888',
                'address'      => 'Jl. Thamrin No. 1, Jakarta Pusat',
            ]
        );

        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );

        return $user;
    }

    public function test_customer_dashboard_page_is_accessible_by_authenticated_customer(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Beranda', false);
        $response->assertSee('Akses Cepat', false);
    }

    public function test_customer_dashboard_page_renders_kpi_cards_with_customer_labels_and_drag_handles(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(200);

        // Drag handles
        $response->assertSee('dashboard-kpi-drag-handle');

        // Customer specific KPI labels
        $response->assertSee('Pesanan Aktif');
        $response->assertSee('Total Pesanan');
        $response->assertSee('Total Pengeluaran');
        $response->assertSee('Status Profil');

        // KPI Wrappers
        $response->assertSee('data-kpi-wrapper="W-KPI-01"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-02"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-03"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-04"', false);
    }

    public function test_customer_can_save_custom_dashboard_kpi_order_and_component_styles(): void
    {
        $customer = $this->createCustomerUser();

        $customOrder = ['W-KPI-04', 'W-KPI-03', 'W-KPI-01', 'W-KPI-02'];
        $customStyles = [
            'W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16],
            'W-KPI-01' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
            'W-QUICK'  => ['shape' => 'rounded', 'width' => 850, 'height' => 180, 'border_radius' => 24],
        ];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => $customOrder,
                'component_styles'    => [
                    'dashboard' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customOrder, $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['dashboard']['W-KPI-04']['width']);
        $this->assertEquals('circle', $matrix['component_styles']['dashboard']['W-KPI-01']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['dashboard']['W-QUICK']['shape']);
    }

    public function test_saved_customer_dashboard_order_and_styles_persist_when_loading_page(): void
    {
        $customer = $this->createCustomerUser();

        $customOrder = ['W-KPI-04', 'W-KPI-03', 'W-KPI-01', 'W-KPI-02'];
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['dashboard_kpi_order'] = $customOrder;
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(200);
        $content = $response->getContent();

        $pos04 = strpos($content, 'data-kpi-wrapper="W-KPI-04"');
        $pos03 = strpos($content, 'data-kpi-wrapper="W-KPI-03"');
        $pos01 = strpos($content, 'data-kpi-wrapper="W-KPI-01"');
        $pos02 = strpos($content, 'data-kpi-wrapper="W-KPI-02"');

        $this->assertTrue($pos04 < $pos03, 'W-KPI-04 should appear before W-KPI-03');
        $this->assertTrue($pos03 < $pos01, 'W-KPI-03 should appear before W-KPI-01');
        $this->assertTrue($pos01 < $pos02, 'W-KPI-01 should appear before W-KPI-02');
    }

    public function test_customer_dashboard_layout_user_isolation(): void
    {
        $customerA = $this->createCustomerUser();
        $customerB = $this->createCustomerUser();

        // Customer A saves custom order
        $orderA = ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'];
        $this->actingAs($customerA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => $orderA,
            ],
        ]);

        // Customer B loads dashboard -> should have default order
        $responseB = $this->actingAs($customerB)->get('/dashboard');
        $responseB->assertStatus(200);

        $contentB = $responseB->getContent();
        $pos01 = strpos($contentB, 'data-kpi-wrapper="W-KPI-01"');
        $pos02 = strpos($contentB, 'data-kpi-wrapper="W-KPI-02"');
        $pos03 = strpos($contentB, 'data-kpi-wrapper="W-KPI-03"');
        $pos04 = strpos($contentB, 'data-kpi-wrapper="W-KPI-04"');

        $this->assertTrue($pos01 < $pos02, 'Customer B should see default order: W-KPI-01 before W-KPI-02');
        $this->assertTrue($pos02 < $pos03, 'Customer B should see default order: W-KPI-02 before W-KPI-03');
        $this->assertTrue($pos03 < $pos04, 'Customer B should see default order: W-KPI-03 before W-KPI-04');
    }

    public function test_customer_dashboard_layout_page_isolation_with_all_other_pages(): void
    {
        $customer = $this->createCustomerUser();

        // Save preferences across all other modules first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $pref->layout_matrix = [
            'finance_kpi_order'     => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'     => ['done', 'packing', 'cooking', 'waiting'],
            'delivery_kpi_order'    => ['done', 'total', 'on_delivery', 'waiting'],
            'analytics_kpi_order'   => ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'],
            'orders_kpi_order'      => ['completed', 'on_delivery', 'preparing', 'pending'],
            'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
            'component_styles'      => [
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'orders'    => ['completed'=> ['shape' => 'pill', 'width' => 240, 'height' => 120, 'border_radius' => 9999]],
                'profile'   => ['P-HEADER' => ['shape' => 'rounded', 'width' => 880, 'height' => 130, 'border_radius' => 24]],
            ],
        ];
        $pref->save();

        // Save Customer Dashboard layout
        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
                'component_styles'    => [
                    'dashboard' => [
                        'W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Verify other pages' keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['done', 'packing', 'cooking', 'waiting'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['done', 'total', 'on_delivery', 'waiting'], $matrix['delivery_kpi_order']);
        $this->assertEquals(['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'], $matrix['analytics_kpi_order']);
        $this->assertEquals(['completed', 'on_delivery', 'preparing', 'pending'], $matrix['orders_kpi_order']);
        $this->assertEquals(['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'], $matrix['profile_sidebar_order']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['orders']['completed']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['profile']['P-HEADER']['shape']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_customer_dashboard_layout_can_be_reset_to_default(): void
    {
        $customer = $this->createCustomerUser();

        // Save custom layout
        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order'   => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
                'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
                'component_styles'      => [
                    'dashboard' => ['W-KPI-01' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
                    'profile'   => ['P-HEADER' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
                ],
            ],
        ]);

        // Reset only dashboard
        $response = $this->actingAs($customer)->postJson(route('workspace.reset-layout'), [
            'page' => 'dashboard',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Dashboard reset
        $this->assertEquals(['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('rectangle', $matrix['component_styles']['dashboard']['W-KPI-01']['shape']);

        // Profile remains intact
        $this->assertEquals(['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'], $matrix['profile_sidebar_order']);
        $this->assertEquals('circle', $matrix['component_styles']['profile']['P-HEADER']['shape']);
    }

    public function test_customer_can_save_and_persist_quick_actions_order(): void
    {
        $customer = $this->createCustomerUser();

        $customQA = ['orders', 'cart', 'catalog', 'profile'];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'quick_actions_order' => $customQA,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $this->assertEquals($customQA, $pref->layout_matrix['quick_actions_order']);
    }

    public function test_partial_or_invalid_customer_dashboard_kpi_order_is_sanitized_cleanly(): void
    {
        $customer = $this->createCustomerUser();

        // Partial order with duplicates and unknown keys
        $incomingOrder = ['W-KPI-03', 'UNKNOWN_KEY', 'W-KPI-03', 'W-KPI-01'];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => $incomingOrder,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $pref->layout_matrix;

        $this->assertEquals(['W-KPI-03', 'W-KPI-01', 'W-KPI-02', 'W-KPI-04'], $matrix['dashboard_kpi_order']);
        $this->assertNotContains('UNKNOWN_KEY', $matrix['dashboard_kpi_order']);
    }

    public function test_customer_business_data_and_navigation_remain_unaltered_and_functional(): void
    {
        $customer = $this->createCustomerUser();

        // Create an order for customer
        $order = Order::create([
            'user_id'         => $customer->id,
            'order_number'    => 'ORD-CUST-9901',
            'status'          => \App\Enums\OrderStatus::PROCESSING,
            'delivery_address'=> 'Jl. Sudirman Kav 50, Jakarta',
            'subtotal'        => 150000,
            'delivery_fee'    => 15000,
            'tax'             => 16500,
            'grand_total'     => 181500,
        ]);

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('ORD-CUST-9901');
        $response->assertSee('181.500');
        $response->assertSee(route('orders.show', $order->id));
        $response->assertSee(route('menus.index'));
        $response->assertSee(route('cart.index'));
    }
}
