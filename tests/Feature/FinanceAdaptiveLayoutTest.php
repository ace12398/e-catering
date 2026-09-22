<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_' . uniqid(),
        ]);
    }

    public function test_finance_page_renders_kpi_cards_with_default_order_and_drag_handles(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/finance');

        $response->assertStatus(200);
        $response->assertSee('Keuangan &amp; Verifikasi Pembayaran', false);
        $response->assertSee('finance-kpi-drag-handle');
        $response->assertSee('data-kpi-wrapper="lunas"', false);
        $response->assertSee('data-kpi-wrapper="menunggu"', false);
        $response->assertSee('data-kpi-wrapper="belum_bayar"', false);
        $response->assertSee('data-kpi-wrapper="ditolak"', false);
    }

    public function test_user_can_save_custom_finance_kpi_order_and_styles(): void
    {
        $admin = $this->createAdminUser();

        $customOrder = ['ditolak', 'lunas', 'belum_bayar', 'menunggu'];
        $customStyles = [
            'ditolak' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 12],
            'lunas'   => ['shape' => 'rounded', 'width' => 260, 'height' => 130, 'border_radius' => 24],
        ];

        $response = $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'finance_kpi_order' => $customOrder,
                'component_styles'  => [
                    'finance' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customOrder, $matrix['finance_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals(280, $matrix['component_styles']['finance']['ditolak']['width']);
    }

    public function test_saved_finance_order_and_styles_persist_when_loading_finance_page(): void
    {
        $admin = $this->createAdminUser();

        $customOrder = ['ditolak', 'belum_bayar', 'lunas', 'menunggu'];
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $admin->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['finance_kpi_order'] = $customOrder;
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($admin)->get('/finance');

        $response->assertStatus(200);
        // Verify order in HTML output: 'ditolak' should appear before 'belum_bayar', before 'lunas', before 'menunggu'
        $content = $response->getContent();
        $posDitolak   = strpos($content, 'data-kpi-wrapper="ditolak"');
        $posBelum     = strpos($content, 'data-kpi-wrapper="belum_bayar"');
        $posLunas     = strpos($content, 'data-kpi-wrapper="lunas"');
        $posMenunggu  = strpos($content, 'data-kpi-wrapper="menunggu"');

        $this->assertTrue($posDitolak < $posBelum, 'Ditolak should appear before Belum Dibayar');
        $this->assertTrue($posBelum < $posLunas, 'Belum Dibayar should appear before Lunas');
        $this->assertTrue($posLunas < $posMenunggu, 'Lunas should appear before Menunggu');
    }

    public function test_finance_layout_user_isolation(): void
    {
        $adminA = $this->createAdminUser();
        $adminB = $this->createAdminUser();

        // Admin A saves custom order
        $orderA = ['ditolak', 'lunas', 'belum_bayar', 'menunggu'];
        $this->actingAs($adminA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'finance_kpi_order' => $orderA,
            ],
        ]);

        // Admin B checks /finance -> should have default order
        $responseB = $this->actingAs($adminB)->get('/finance');
        $responseB->assertStatus(200);

        $contentB = $responseB->getContent();
        $posLunas    = strpos($contentB, 'data-kpi-wrapper="lunas"');
        $posMenunggu = strpos($contentB, 'data-kpi-wrapper="menunggu"');
        $posBelum    = strpos($contentB, 'data-kpi-wrapper="belum_bayar"');
        $posDitolak  = strpos($contentB, 'data-kpi-wrapper="ditolak"');

        $this->assertTrue($posLunas < $posMenunggu, 'Admin B should have default order: Lunas before Menunggu');
        $this->assertTrue($posMenunggu < $posBelum, 'Admin B should have default order: Menunggu before Belum Bayar');
        $this->assertTrue($posBelum < $posDitolak, 'Admin B should have default order: Belum Bayar before Ditolak');
    }

    public function test_finance_layout_page_isolation(): void
    {
        $admin = $this->createAdminUser();

        $defaultDashboardWidgets = WorkspacePreference::getDefaultLayoutMatrix()['dashboard_widgets'];

        // Saving finance order should not modify dashboard widgets
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'finance_kpi_order' => ['ditolak', 'lunas', 'belum_bayar', 'menunggu'],
            ],
        ]);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertEquals($defaultDashboardWidgets, $pref->effective_layout_matrix['dashboard_widgets']);
    }

    public function test_finance_layout_can_be_reset_to_default(): void
    {
        $admin = $this->createAdminUser();

        // First, save a custom order
        $this->actingAs($admin)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'finance_kpi_order' => ['ditolak', 'lunas', 'belum_bayar', 'menunggu'],
                'component_styles'  => [
                    'finance' => [
                        'lunas' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12],
                    ],
                ],
            ],
        ]);

        // Then reset finance page layout
        $resetResponse = $this->actingAs($admin)->postJson(route('workspace.reset-layout'), [
            'page' => 'finance',
        ]);

        $resetResponse->assertStatus(200);
        $resetResponse->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $admin->id)->first();
        $this->assertEquals(
            ['lunas', 'menunggu', 'belum_bayar', 'ditolak'],
            $pref->layout_matrix['finance_kpi_order']
        );
        $this->assertEquals(
            'rectangle',
            $pref->layout_matrix['component_styles']['finance']['lunas']['shape']
        );
    }
}
