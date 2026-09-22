<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenusAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
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

    protected function createMenuFixtures(): array
    {
        $vendor = \App\Models\Vendor::create([
            'name' => 'Dapur Utama',
            'slug' => 'dapur-utama',
            'phone' => '08123456789',
            'rating' => 4.8,
            'is_active' => true,
        ]);

        $cat1 = Category::create(['name' => 'Nasi Box', 'slug' => 'nasi-box', 'is_active' => true, 'icon' => '🍱']);
        $cat2 = Category::create(['name' => 'Prasmanan', 'slug' => 'prasmanan', 'is_active' => true, 'icon' => '🍲']);
        $cat3 = Category::create(['name' => 'Snack Box', 'slug' => 'snack-box', 'is_active' => true, 'icon' => '🥐']);

        $menu1 = Menu::create([
            'name' => 'Nasi Ayam Bakar',
            'slug' => 'nasi-ayam-bakar',
            'description' => 'Ayam bakar madu lezat',
            'price' => 35000,
            'category_id' => $cat1->id,
            'vendor_id' => $vendor->id,
            'calories' => 550,
            'is_available' => true,
            'is_halal' => true,
        ]);

        $menu2 = Menu::create([
            'name' => 'Paket Prasmanan Mewah',
            'slug' => 'paket-prasmanan-mewah',
            'description' => 'Menu lengkap prasmanan',
            'price' => 75000,
            'category_id' => $cat2->id,
            'vendor_id' => $vendor->id,
            'calories' => 850,
            'is_available' => true,
            'is_halal' => true,
        ]);

        $menu3 = Menu::create([
            'name' => 'Snack Box Manis',
            'slug' => 'snack-box-manis',
            'description' => 'Kue tradisional & modern',
            'price' => 20000,
            'category_id' => $cat3->id,
            'vendor_id' => $vendor->id,
            'calories' => 350,
            'is_available' => true,
            'is_halal' => true,
        ]);

        return compact('vendor', 'cat1', 'cat2', 'cat3', 'menu1', 'menu2', 'menu3');
    }

    public function test_menus_page_is_accessible_by_authenticated_user(): void
    {
        $user = $this->createUser();
        $this->createMenuFixtures();

        $response = $this->actingAs($user)->get('/menus');

        $response->assertStatus(200);
        $response->assertSee('Katalog Paket Menu Katering', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Katalog Menu', false);
    }

    public function test_menus_page_renders_menu_items_with_default_order_and_drag_handles(): void
    {
        $user = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        $response = $this->actingAs($user)->get('/menus');

        $response->assertStatus(200);
        $response->assertSee('menu-card-drag-handle');
        $response->assertSee('menus-cat-drag-handle');
        $response->assertSee('data-menu-wrapper="' . $fixtures['menu1']->id . '"', false);
        $response->assertSee('data-menu-wrapper="' . $fixtures['menu2']->id . '"', false);
        $response->assertSee('data-component-id="menu_item_' . $fixtures['menu1']->id . '"', false);
    }

    public function test_user_can_save_custom_menus_order_categories_and_styles(): void
    {
        $user = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        $customItemOrder = [$fixtures['menu3']->id, $fixtures['menu1']->id, $fixtures['menu2']->id];
        $customCatOrder  = ['snack-box', 'nasi-box', 'prasmanan'];
        $customStyles    = [
            'menu_item_' . $fixtures['menu1']->id => ['shape' => 'hexagon', 'width' => 320, 'height' => 280, 'border_radius' => 16],
            'category_pills'                       => ['shape' => 'sharp', 'width' => 200, 'height' => 44, 'border_radius' => 0],
            'M-HEADER'                             => ['shape' => 'rounded', 'width' => 880, 'height' => 130, 'border_radius' => 24],
        ];

        $response = $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_items_order'      => $customItemOrder,
                'menu_categories_order' => $customCatOrder,
                'component_styles'      => [
                    'menus' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;
        $this->assertEquals($customItemOrder, $matrix['menu_items_order']);
        $this->assertEquals($customCatOrder, $matrix['menu_categories_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['menus']['menu_item_' . $fixtures['menu1']->id]['shape']);
        $this->assertEquals(320, $matrix['component_styles']['menus']['menu_item_' . $fixtures['menu1']->id]['width']);
    }

    public function test_saved_menus_order_and_styles_persist_when_loading_menus_page(): void
    {
        $user = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        $customItemOrder = [$fixtures['menu3']->id, $fixtures['menu1']->id, $fixtures['menu2']->id];
        $customCatOrder  = ['snack-box', 'nasi-box', 'prasmanan'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['menu_items_order'] = $customItemOrder;
        $matrix['menu_categories_order'] = $customCatOrder;
        $matrix['component_styles']['menus'] = [
            'menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 220, 'height' => 220, 'border_radius' => 16],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($user)->get('/menus');

        $response->assertStatus(200);
        // Verify category order in view
        $content = $response->getContent();
        $posCat3 = strpos($content, 'data-cat-slug="snack-box"');
        $posCat1 = strpos($content, 'data-cat-slug="nasi-box"');
        $posCat2 = strpos($content, 'data-cat-slug="prasmanan"');
        $this->assertTrue($posCat3 < $posCat1 && $posCat1 < $posCat2, 'Categories must render in saved custom order');

        // Verify menu item order in view
        $posMenu3 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu3']->id . '"');
        $posMenu1 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu1']->id . '"');
        $posMenu2 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu2']->id . '"');
        $this->assertTrue($posMenu3 < $posMenu1 && $posMenu1 < $posMenu2, 'Menu cards must render in saved custom order');
    }

    public function test_menus_layout_user_isolation(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        // User A saves custom order
        $customItemOrderA = [$fixtures['menu3']->id, $fixtures['menu2']->id, $fixtures['menu1']->id];
        $this->actingAs($userA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_items_order' => $customItemOrderA,
                'component_styles' => [
                    'menus' => [
                        'menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Verify User B's preference is untouched
        $prefB = WorkspacePreference::where('user_id', $userB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('menu_items_order', $matrixB);

        // User B loads menus page
        $responseB = $this->actingAs($userB)->get('/menus');
        $responseB->assertStatus(200);
    }

    public function test_menus_layout_page_isolation_with_finance_and_dashboard(): void
    {
        $user = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        // Save Dashboard & Finance layout first
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'   => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
            ],
        ];
        $pref->save();

        // Save Menus layout
        $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_items_order' => [$fixtures['menu2']->id, $fixtures['menu1']->id, $fixtures['menu3']->id],
                'component_styles' => [
                    'menus' => [
                        'menu_item_' . $fixtures['menu2']->id => ['shape' => 'pill', 'width' => 320, 'height' => 260, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Verify Dashboard & Finance keys are completely intact
        $prefFresh = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['menus']['menu_item_' . $fixtures['menu2']->id]['shape']);
    }

    public function test_menus_layout_can_be_reset_to_default(): void
    {
        $user = $this->createUser();
        $fixtures = $this->createMenuFixtures();

        // Save custom menu layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'   => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'menu_items_order'      => [$fixtures['menu3']->id, $fixtures['menu1']->id],
            'menu_categories_order' => ['snack-box', 'nasi-box'],
            'component_styles'      => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'menus'     => ['menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16]],
            ],
        ];
        $pref->save();

        // Reset only menus page
        $response = $this->actingAs($user)->postJson(route('workspace.reset-layout'), [
            'page' => 'menus',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Menus specific keys are reset to default
        $this->assertEmpty($matrix['menu_items_order']);
        $this->assertEmpty($matrix['menu_categories_order']);

        // Dashboard keys remain untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_partial_or_invalid_menu_order_is_sanitized_cleanly(): void
    {
        $user = $this->createUser();

        // Invalid: strings, negative numbers, duplicates, unknown types
        $incomingOrder = [999, -5, 'foo', 999, '25', 10];

        $response = $this->actingAs($user)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_items_order' => $incomingOrder,
            ],
        ]);

        $response->assertStatus(200);

        $pref = WorkspacePreference::where('user_id', $user->id)->first();
        $matrix = $pref->layout_matrix;

        // Sanitized should only contain unique positive integers
        $this->assertEquals([999, 25, 10], $matrix['menu_items_order']);
    }
}
