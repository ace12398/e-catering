<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomerUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'customer',
            'username' => 'user_' . uniqid(),
        ], $attributes));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'PT Maju Bersama',
                'phone_number' => '081234567890',
                'address'      => 'Jl. Sudirman No. 10, Jakarta Selatan',
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
                'phone_number' => '081987654321',
                'address'      => 'Jl. Thamrin No. 1, Jakarta Pusat',
            ]
        );

        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );

        return $user;
    }

    public function test_profile_page_is_accessible_by_authenticated_user_and_admin(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Profil Saya', false);
        $response->assertSee('Informasi Profil Pengguna', false);
        $response->assertSee('Simpan Perubahan Profil', false);
    }

    public function test_profile_page_renders_components_with_drag_handles_and_wrappers(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);

        // Drag handles
        $response->assertSee('profile-sidebar-drag-handle');

        // Components & Wrappers
        $response->assertSee('id="profile-wrapper-P-HEADER"', false);
        $response->assertSee('id="profile-wrapper-P-FORM"', false);
        $response->assertSee('data-profile-card="P-COMPLETION"', false);
        $response->assertSee('data-profile-card="P-WORKSPACE"', false);
        $response->assertSee('data-profile-card="P-ACTIVITY"', false);
    }

    public function test_user_can_save_custom_profile_sidebar_order_and_styles(): void
    {
        $user = $this->createCustomerUser();

        $customSidebarOrder = ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'];
        $customStyles = [
            'P-HEADER'     => ['shape' => 'rounded', 'width' => 850, 'height' => 130, 'border_radius' => 24],
            'P-FORM'       => ['shape' => 'hexagon', 'width' => 650, 'height' => 450, 'border_radius' => 16],
            'P-COMPLETION' => ['shape' => 'pill', 'width' => 300, 'height' => 220, 'border_radius' => 9999],
        ];

        $response = $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => $customSidebarOrder,
                'component_styles'      => [
                    'profile' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customSidebarOrder, $matrix['profile_sidebar_order']);
        $this->assertEquals('rounded', $matrix['component_styles']['profile']['P-HEADER']['shape']);
        $this->assertEquals(850, $matrix['component_styles']['profile']['P-HEADER']['width']);
        $this->assertEquals('hexagon', $matrix['component_styles']['profile']['P-FORM']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['profile']['P-COMPLETION']['shape']);
    }

    public function test_saved_profile_order_and_styles_persist_when_loading_profile_page(): void
    {
        $user = $this->createCustomerUser();

        $customSidebarOrder = ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['profile_sidebar_order'] = $customSidebarOrder;
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify Sidebar order in DOM
        $posActivity   = strpos($content, 'data-profile-card="P-ACTIVITY"');
        $posWorkspace  = strpos($content, 'data-profile-card="P-WORKSPACE"');
        $posCompletion = strpos($content, 'data-profile-card="P-COMPLETION"');

        $this->assertTrue($posActivity < $posWorkspace, 'P-ACTIVITY must render before P-WORKSPACE');
        $this->assertTrue($posWorkspace < $posCompletion, 'P-WORKSPACE must render before P-COMPLETION');
    }

    public function test_profile_layout_user_isolation(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser();

        // User A saves custom order
        $orderA = ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'];
        $this->actingAs($userA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => $orderA,
                'component_styles'      => [
                    'profile' => [
                        'P-HEADER' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Verify User B's layout matrix does not contain User A's changes
        $prefB = WorkspacePreference::where('user_id', $userB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('profile_sidebar_order', $matrixB);

        // User B loads profile page
        $responseB = $this->actingAs($userB)->get('/profile');
        $responseB->assertStatus(200);
    }

    public function test_profile_layout_page_isolation_with_all_other_pages(): void
    {
        $user = $this->createCustomerUser();

        // Save Dashboard, Finance, Menus, Kitchen, Delivery, Analytics, Orders first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'   => ['done', 'packing', 'cooking', 'waiting'],
            'delivery_kpi_order'  => ['done', 'total', 'on_delivery', 'waiting'],
            'analytics_kpi_order' => ['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'],
            'orders_kpi_order'    => ['completed', 'on_delivery', 'preparing', 'pending'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
                'kitchen'   => ['done'     => ['shape' => 'pill', 'width' => 300, 'height' => 120, 'border_radius' => 9999]],
                'delivery'  => ['done'     => ['shape' => 'sharp', 'width' => 250, 'height' => 110, 'border_radius' => 0]],
                'analytics' => ['A-KPI-06' => ['shape' => 'rounded', 'width' => 260, 'height' => 130, 'border_radius' => 24]],
                'orders'    => ['completed'=> ['shape' => 'circle', 'width' => 180, 'height' => 180, 'border_radius' => 16]],
            ],
        ];
        $pref->save();

        // Save Profile layout
        $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
                'component_styles'      => [
                    'profile' => [
                        'P-HEADER' => ['shape' => 'rounded', 'width' => 880, 'height' => 130, 'border_radius' => 24],
                    ],
                ],
            ],
        ]);

        // Verify other pages' keys are intact
        $prefFresh = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['done', 'packing', 'cooking', 'waiting'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['done', 'total', 'on_delivery', 'waiting'], $matrix['delivery_kpi_order']);
        $this->assertEquals(['A-KPI-06', 'A-KPI-05', 'A-KPI-04', 'A-KPI-03', 'A-KPI-02', 'A-KPI-01'], $matrix['analytics_kpi_order']);
        $this->assertEquals(['completed', 'on_delivery', 'preparing', 'pending'], $matrix['orders_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['kitchen']['done']['shape']);
        $this->assertEquals('sharp', $matrix['component_styles']['delivery']['done']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['analytics']['A-KPI-06']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['orders']['completed']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['profile']['P-HEADER']['shape']);
    }

    public function test_profile_layout_can_be_reset_to_default(): void
    {
        $user = $this->createCustomerUser();

        // Save custom profile layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'   => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
            'component_styles'      => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'profile'   => ['P-HEADER' => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
            ],
        ];
        $pref->save();

        // Reset only profile page
        $response = $this->actingAs($user)->postJson(route('workspace.reset-layout'), [
            'page' => 'profile',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Profile specific keys reset to default
        $this->assertEquals(['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'], $matrix['profile_sidebar_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_profile_sidebar_order_is_sanitized_cleanly(): void
    {
        $user = $this->createCustomerUser();

        // Invalid: partial, duplicates, unknown component
        $incomingOrder = ['P-ACTIVITY', 'UNKNOWN_CARD', 'P-ACTIVITY'];

        $response = $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => $incomingOrder,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized orders should contain unique valid keys filled with defaults
        $this->assertEquals(['P-ACTIVITY', 'P-COMPLETION', 'P-WORKSPACE'], $matrix['profile_sidebar_order']);
    }

    public function test_profile_business_data_and_update_workflow_remain_unaltered_and_functional(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->patch('/profile/update', [
            'name'         => 'Budi Setiawan',
            'company_name' => 'PT Nusantara Sejahtera',
            'phone_number' => '081299998888',
            'address'      => 'Gedung Wisma 46 Lt. 12, Jakarta',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Informasi profil berhasil diperbarui!');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Budi Setiawan']);
        $this->assertDatabaseHas('profiles', [
            'user_id'      => $user->id,
            'company_name' => 'PT Nusantara Sejahtera',
            'phone_number' => '081299998888',
            'address'      => 'Gedung Wisma 46 Lt. 12, Jakarta',
        ]);
    }
}
