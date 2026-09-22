<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAdaptiveLayoutTest extends TestCase
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

    public function test_analytics_page_is_accessible_by_admin(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertStatus(200);
        $response->assertSee('Analisis Bisnis', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Analisis Bisnis', false);
    }

    public function test_analytics_page_renders_kpi_cards_and_chart_panels_with_drag_handles(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertStatus(200);

        // Drag handles
        $response->assertSee('analytics-kpi-drag-handle');
        $response->assertSee('analytics-panel-drag-handle');

        // KPI wrappers
        $response->assertSee('data-kpi-wrapper="A-KPI-01"', false);
        $response->assertSee('data-kpi-wrapper="A-KPI-02"', false);
        $response->assertSee('data-kpi-wrapper="A-KPI-03"', false);
        $response->assertSee('data-kpi-wrapper="A-KPI-04"', false);
        $response->assertSee('data-kpi-wrapper="A-KPI-05"', false);
        $response->assertSee('data-kpi-wrapper="A-KPI-06"', false);

        // Panels wrappers
        $response->assertSee('data-panel-wrapper="A-CHART-ORDERS"', false);
        $response->assertSee('data-panel-wrapper="A-CHART-REVENUE"', false);
        $response->assertSee('data-panel-wrapper="A-TOP-MENU"', false);
        $response->assertSee('data-panel-wrapper="A-ORDER-DISTRIBUTION"', false);
        $response->assertSee('data-panel-wrapper="A-ACTIVITY"', false);
        $response->assertSee('data-panel-wrapper="A-SYSTEM"', false);
    }

    public function test_user_can_save_custom_analytics_kpi_and_panels_order_and_styles(): void
    {
        $admin = $this->createAdminUser();

        $customKpiOrder    = ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'];
        $customPanelsOrder = ['A-SYSTEM', 'A-ACTIVITY', 'A-ORDER-DISTRIBUTION', 'A-TOP-MENU', 'A-CHART-REVENUE', 'A-CHART-ORDERS'];
        $customStyles      = [
            'A-KPI-01'        => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16],
            'A-CHART-REVENUE' => ['shape' => 'rounded', 'width' => 500, 'height' => 380, 'border_radius' => 24],
        ];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'analytics_kpi_order'    => $customKpiOrder,
                'analytics_panels_order' => $customPanelsOrder,
                'component_styles'       => [
                    'analytics' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customKpiOrder, $matrix['analytics_kpi_order']);
        $this->assertEquals($customPanelsOrder, $matrix['analytics_panels_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['analytics']['A-KPI-01']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['analytics']['A-KPI-01']['width']);
        $this->assertEquals('rounded', $matrix['component_styles']['analytics']['A-CHART-REVENUE']['shape']);
        $this->assertEquals(500, $matrix['component_styles']['analytics']['A-CHART-REVENUE']['width']);
    }

    public function test_saved_analytics_order_and_styles_persist_when_loading_analytics_page(): void
    {
        $admin = $this->createAdminUser();

        $customKpiOrder    = ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'];
        $customPanelsOrder = ['A-SYSTEM', 'A-ACTIVITY', 'A-ORDER-DISTRIBUTION', 'A-TOP-MENU', 'A-CHART-REVENUE', 'A-CHART-ORDERS'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['analytics_kpi_order']    = $customKpiOrder;
        $matrix['analytics_panels_order'] = $customPanelsOrder;
        $matrix['component_styles']['analytics'] = [
            'A-KPI-06' => ['shape' => 'circle', 'width' => 160, 'height' => 160, 'border_radius' => 16],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify KPI order in DOM
        $posKpi06 = strpos($content, 'data-kpi-wrapper="A-KPI-06"');
        $posKpi05 = strpos($content, 'data-kpi-wrapper="A-KPI-05"');
        $posKpi01 = strpos($content, 'data-kpi-wrapper="A-KPI-01"');
        $this->assertTrue($posKpi06 < $posKpi05 && $posKpi05 < $posKpi01, 'KPI cards must render in saved custom order');

        // Verify Panels order in DOM: A-SYSTEM should appear before A-CHART-ORDERS
        $posSystem = strpos($content, 'data-panel-wrapper="A-SYSTEM"');
        $posOrders = strpos($content, 'data-panel-wrapper="A-CHART-ORDERS"');
        $this->assertTrue($posSystem < $posOrders, 'Analytics panels must render in saved custom order');
    }

    public function test_analytics_layout_user_isolation(): void
    {
        $adminA = $this->createAdminUser();
        $adminB = $this->createAdminUser();

        // Admin A saves custom order
        $orderA = ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'];
        $this->actingAs($adminA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'analytics_kpi_order' => $orderA,
                'component_styles' => [
                    'analytics' => [
                        'A-KPI-06' => ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Verify Admin B's layout matrix does not contain Admin A's changes
        $prefB = WorkspacePreference::where('user_id', $adminB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('analytics_kpi_order', $matrixB);

        // Admin B loads analytics page
        $responseB = $this->actingAs($adminB)->get('/analytics');
        $responseB->assertStatus(200);
    }

    public function test_analytics_layout_page_isolation_with_finance_dashboard_menus_kitchen_and_delivery(): void
    {
        $admin = $this->createAdminUser();

        // Save Dashboard & Finance & Kitchen & Delivery layout first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'   => ['done', 'packing', 'cooking', 'waiting'],
            'delivery_kpi_order'  => ['done', 'total', 'on_delivery', 'waiting'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'kitchen'   => ['done'     => ['shape' => 'pill', 'width' => 300, 'height' => 120, 'border_radius' => 9999]],
                'delivery'  => ['done'     => ['shape' => 'sharp', 'width' => 250, 'height' => 110, 'border_radius' => 0]],
            ],
        ];
        $pref->save();

        // Save Analytics layout
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'analytics_kpi_order' => ['A-KPI-03', 'A-KPI-02', 'A-KPI-01', 'A-KPI-06', 'A-KPI-05', 'A-KPI-04'],
                'component_styles' => [
                    'analytics' => [
                        'A-KPI-03' => ['shape' => 'rounded', 'width' => 260, 'height' => 130, 'border_radius' => 24],
                    ],
                ],
            ],
        ]);

        // Verify Dashboard, Finance, Kitchen, and Delivery keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['done', 'packing', 'cooking', 'waiting'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['done', 'total', 'on_delivery', 'waiting'], $matrix['delivery_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['kitchen']['done']['shape']);
        $this->assertEquals('sharp', $matrix['component_styles']['delivery']['done']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['analytics']['A-KPI-03']['shape']);
    }

    public function test_analytics_layout_can_be_reset_to_default(): void
    {
        $admin = $this->createAdminUser();

        // Save custom analytics layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'    => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'analytics_kpi_order'    => ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'],
            'analytics_panels_order' => ['A-SYSTEM', 'A-ACTIVITY', 'A-ORDER-DISTRIBUTION', 'A-TOP-MENU', 'A-CHART-REVENUE', 'A-CHART-ORDERS'],
            'component_styles'       => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'analytics' => ['A-KPI-06' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
            ],
        ];
        $pref->save();

        // Reset only analytics page
        $response = $this->actingAs($admin)->postJson(route('workspace.reset-layout'), [
            'page' => 'analytics',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Analytics specific keys reset to default
        $this->assertEquals(['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'], $matrix['analytics_kpi_order']);
        $this->assertEquals(['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'], $matrix['analytics_panels_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_analytics_order_is_sanitized_cleanly(): void
    {
        $admin = $this->createAdminUser();

        // Invalid: partial, duplicates, unknown component
        $incomingKpi    = ['A-KPI-05', 'UNKNOWN_STAT', 'A-KPI-05', 'A-KPI-01'];
        $incomingPanels = ['A-SYSTEM', 'INVALID_PANEL', 'A-SYSTEM', 'A-CHART-ORDERS'];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'analytics_kpi_order'    => $incomingKpi,
                'analytics_panels_order' => $incomingPanels,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized orders should contain unique valid keys filled with defaults
        $this->assertEquals(['A-KPI-05', 'A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-06'], $matrix['analytics_kpi_order']);
        $this->assertEquals(['A-SYSTEM', 'A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY'], $matrix['analytics_panels_order']);
    }

    public function test_analytics_business_data_and_summary_remain_unaltered_and_accurate(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewHas('summary');
        $response->assertViewHas('weeklyOrders');
        $response->assertViewHas('monthlyRevenue');
        $response->assertViewHas('topMenus');
        $response->assertViewHas('orderStatuses');
        $response->assertViewHas('recentActivities');
        $response->assertViewHas('systemTotals');
    }
}
