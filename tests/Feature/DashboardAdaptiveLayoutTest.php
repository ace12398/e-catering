<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAdaptiveLayoutTest extends TestCase
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
            'username' => 'cust_' . uniqid(),
        ]);
        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );
        return $user;
    }

    public function test_dashboard_page_is_accessible_by_authenticated_user(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Beranda', false);
    }

    public function test_dashboard_page_renders_kpi_cards_with_default_order_and_drag_handles(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dashboard-kpi-drag-handle');
        $response->assertSee('data-kpi-wrapper="W-KPI-01"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-02"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-03"', false);
        $response->assertSee('data-kpi-wrapper="W-KPI-04"', false);
    }

    public function test_user_can_save_custom_dashboard_kpi_order_and_styles(): void
    {
        $user = $this->createAdminUser();

        $customOrder = ['W-KPI-04', 'W-KPI-01', 'W-KPI-03', 'W-KPI-02'];
        $customStyles = [
            'W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16],
            'W-KPI-01' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
        ];

        $response = $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => $customOrder,
                'component_styles'  => [
                    'dashboard' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customOrder, $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['dashboard']['W-KPI-04']['width']);
    }

    public function test_saved_dashboard_order_and_styles_persist_when_loading_dashboard_page(): void
    {
        $user = $this->createAdminUser();

        $customOrder = ['W-KPI-04', 'W-KPI-03', 'W-KPI-01', 'W-KPI-02'];
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['dashboard_kpi_order'] = $customOrder;
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($user)->get('/dashboard');

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

    public function test_dashboard_layout_user_isolation(): void
    {
        $userA = $this->createAdminUser();
        $userB = $this->createAdminUser();

        // User A saves custom order
        $orderA = ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'];
        $this->actingAs($userA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => $orderA,
            ],
        ]);

        // User B loads dashboard -> should have default order
        $responseB = $this->actingAs($userB)->get('/dashboard');
        $responseB->assertStatus(200);

        $contentB = $responseB->getContent();
        $pos01 = strpos($contentB, 'data-kpi-wrapper="W-KPI-01"');
        $pos02 = strpos($contentB, 'data-kpi-wrapper="W-KPI-02"');
        $pos03 = strpos($contentB, 'data-kpi-wrapper="W-KPI-03"');
        $pos04 = strpos($contentB, 'data-kpi-wrapper="W-KPI-04"');

        $this->assertTrue($pos01 < $pos02, 'User B should see default order: W-KPI-01 before W-KPI-02');
        $this->assertTrue($pos02 < $pos03, 'User B should see default order: W-KPI-02 before W-KPI-03');
        $this->assertTrue($pos03 < $pos04, 'User B should see default order: W-KPI-03 before W-KPI-04');
    }

    public function test_dashboard_layout_page_isolation_with_finance(): void
    {
        $admin = $this->createAdminUser();

        $defaultFinanceKpi = WorkspacePreference::getDefaultLayoutMatrix()['finance_kpi_order'];

        // Saving dashboard order should not affect finance order
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            ],
        ]);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertEquals($defaultFinanceKpi, $pref->effective_layout_matrix['finance_kpi_order']);
    }

    public function test_finance_layout_page_isolation_with_dashboard(): void
    {
        $admin = $this->createAdminUser();

        $defaultDashboardKpi = WorkspacePreference::getDefaultLayoutMatrix()['dashboard_kpi_order'];

        // Saving finance order should not affect dashboard order
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'finance_kpi_order' => ['ditolak', 'lunas', 'belum_bayar', 'menunggu'],
            ],
        ]);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertEquals($defaultDashboardKpi, $pref->effective_layout_matrix['dashboard_kpi_order']);
    }

    public function test_dashboard_layout_can_be_reset_to_default(): void
    {
        $user = $this->createAdminUser();

        // First, save a custom order and styles
        $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
                'component_styles'  => [
                    'dashboard' => [
                        'W-KPI-01' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Then reset dashboard page layout
        $resetResponse = $this->actingAs($user)->postJson(route('workspace.reset-layout'), [
            'page' => 'dashboard',
        ]);

        $resetResponse->assertStatus(200);
        $resetResponse->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $this->assertEquals(
            ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'],
            $pref->layout_matrix['dashboard_kpi_order']
        );
        $this->assertEquals(
            'rectangle',
            $pref->layout_matrix['component_styles']['dashboard']['W-KPI-01']['shape']
        );
    }

    public function test_partial_or_invalid_dashboard_kpi_order_is_sanitized_without_missing_keys(): void
    {
        $user = $this->createAdminUser();

        // Send partial order with duplicate and unknown key
        $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'dashboard_kpi_order' => ['W-KPI-03', 'W-KPI-03', 'UNKNOWN_KEY', 'W-KPI-01'],
            ],
        ]);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $savedOrder = $pref->layout_matrix['dashboard_kpi_order'];

        // Should preserve unique valid keys in given order and append missing defaults
        $this->assertEquals(['W-KPI-03', 'W-KPI-01', 'W-KPI-02', 'W-KPI-04'], $savedOrder);
        $this->assertNotContains('UNKNOWN_KEY', $savedOrder);
    }
}
