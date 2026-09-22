<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMenusAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(): User
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'username' => 'customer_' . uniqid(),
            'email' => 'cust_' . uniqid() . '@catering.test',
        ]);
        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );
        return $user;
    }

    protected function createMenuFixtures(): array
    {
        $vendor = Vendor::create([
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
            'description' => 'Ayam bakar madu lezat dan gurih',
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
            'description' => 'Menu lengkap prasmanan untuk resepsi',
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
            'description' => 'Kue basah tradisional dan modern',
            'price' => 20000,
            'category_id' => $cat3->id,
            'vendor_id' => $vendor->id,
            'calories' => 350,
            'is_available' => true,
            'is_halal' => true,
        ]);

        return compact('vendor', 'cat1', 'cat2', 'cat3', 'menu1', 'menu2', 'menu3');
    }

    public function test_customer_can_access_menus_catalog_workspace(): void
    {
        $customer = $this->createCustomer();
        $this->createMenuFixtures();

        $response = $this->actingAs($customer)->get('/menus');

        $response->assertStatus(200);
        $response->assertSee('Katalog Paket Menu Katering', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif', false);
        $response->assertSee('data-menu-section="M-HEADER"', false);
        $response->assertSee('data-menu-section="M-TOOLBAR"', false);
        $response->assertSee('data-menu-section="M-GRID"', false);
        $response->assertSee('menu-section-drag-handle', false);
        $response->assertSee('menus-cat-drag-handle', false);
        $response->assertSee('menu-card-drag-handle', false);
    }

    public function test_menus_renders_default_sections_order_and_items(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        $response = $this->actingAs($customer)->get('/menus');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Check default section order: M-HEADER before M-TOOLBAR before M-GRID
        $posHeader = strpos($content, 'data-menu-section="M-HEADER"');
        $posToolbar = strpos($content, 'data-menu-section="M-TOOLBAR"');
        $posGrid = strpos($content, 'data-menu-section="M-GRID"');

        $this->assertTrue($posHeader !== false && $posToolbar !== false && $posGrid !== false);
        $this->assertTrue($posHeader < $posToolbar && $posToolbar < $posGrid, 'Sections should render in M-HEADER, M-TOOLBAR, M-GRID order by default');

        // Check menu cards exist
        $response->assertSee('data-menu-wrapper="' . $fixtures['menu1']->id . '"', false);
        $response->assertSee('data-menu-wrapper="' . $fixtures['menu2']->id . '"', false);
        $response->assertSee('data-menu-wrapper="' . $fixtures['menu3']->id . '"', false);
    }

    public function test_customer_can_save_custom_sections_categories_items_order_and_styles(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        $customSectionOrder = ['M-TOOLBAR', 'M-HEADER', 'M-GRID'];
        $customItemOrder    = [$fixtures['menu3']->id, $fixtures['menu1']->id, $fixtures['menu2']->id];
        $customCatOrder     = ['snack-box', 'nasi-box', 'prasmanan'];
        $customStyles       = [
            'M-HEADER'  => ['shape' => 'rounded', 'width' => 880, 'height' => 130, 'border_radius' => 24],
            'M-TOOLBAR' => ['shape' => 'rectangle', 'width' => 860, 'height' => 90, 'border_radius' => 16],
            'M-GRID'    => ['shape' => 'sharp', 'width' => 900, 'height' => 480, 'border_radius' => 0],
            'category_pills' => ['shape' => 'pill', 'width' => 200, 'height' => 44, 'border_radius' => 9999],
            'menu_item_' . $fixtures['menu1']->id => ['shape' => 'hexagon', 'width' => 320, 'height' => 280, 'border_radius' => 16],
        ];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_sections_order'   => $customSectionOrder,
                'menu_categories_order' => $customCatOrder,
                'menu_items_order'      => $customItemOrder,
                'component_styles'      => [
                    'menus' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;

        $this->assertEquals($customSectionOrder, $matrix['menu_sections_order']);
        $this->assertEquals($customCatOrder, $matrix['menu_categories_order']);
        $this->assertEquals($customItemOrder, $matrix['menu_items_order']);
        $this->assertEquals('rounded', $matrix['component_styles']['menus']['M-HEADER']['shape']);
        $this->assertEquals('hexagon', $matrix['component_styles']['menus']['menu_item_' . $fixtures['menu1']->id]['shape']);
    }

    public function test_saved_sections_and_menu_items_order_persist_on_page_load(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        $customSectionOrder = ['M-GRID', 'M-TOOLBAR', 'M-HEADER'];
        $customItemOrder    = [$fixtures['menu3']->id, $fixtures['menu2']->id, $fixtures['menu1']->id];
        $customCatOrder     = ['snack-box', 'prasmanan', 'nasi-box'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['menu_sections_order']   = $customSectionOrder;
        $matrix['menu_categories_order'] = $customCatOrder;
        $matrix['menu_items_order']      = $customItemOrder;
        $matrix['component_styles']['menus'] = [
            'M-HEADER' => ['shape' => 'rounded', 'width' => 850, 'height' => 140, 'border_radius' => 24],
            'menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 240, 'height' => 240, 'border_radius' => 16],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $response = $this->actingAs($customer)->get('/menus');

        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Verify custom sections order in DOM (M-GRID -> M-TOOLBAR -> M-HEADER)
        $posGrid    = strpos($content, 'data-menu-section="M-GRID"');
        $posToolbar = strpos($content, 'data-menu-section="M-TOOLBAR"');
        $posHeader  = strpos($content, 'data-menu-section="M-HEADER"');
        $this->assertTrue($posGrid < $posToolbar && $posToolbar < $posHeader, 'Sections must render in saved custom order');

        // 2. Verify custom categories order in DOM
        $posCat3 = strpos($content, 'data-cat-slug="snack-box"');
        $posCat2 = strpos($content, 'data-cat-slug="prasmanan"');
        $posCat1 = strpos($content, 'data-cat-slug="nasi-box"');
        $this->assertTrue($posCat3 < $posCat2 && $posCat2 < $posCat1, 'Categories must render in saved custom order');

        // 3. Verify custom menu items order in DOM (menu3 -> menu2 -> menu1)
        $posMenu3 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu3']->id . '"');
        $posMenu2 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu2']->id . '"');
        $posMenu1 = strpos($content, 'data-menu-wrapper="' . $fixtures['menu1']->id . '"');
        $this->assertTrue($posMenu3 < $posMenu2 && $posMenu2 < $posMenu1, 'Menu cards must render in saved custom order');
    }

    public function test_customer_menus_layout_user_isolation(): void
    {
        $customerA = $this->createCustomer();
        $customerB = $this->createCustomer();
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'adm_' . uniqid()]);
        $fixtures = $this->createMenuFixtures();

        // Customer A saves custom section and item order
        $customSectionOrderA = ['M-TOOLBAR', 'M-GRID', 'M-HEADER'];
        $customItemOrderA    = [$fixtures['menu3']->id, $fixtures['menu2']->id, $fixtures['menu1']->id];
        $this->actingAs($customerA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_sections_order' => $customSectionOrderA,
                'menu_items_order'    => $customItemOrderA,
                'component_styles'    => [
                    'menus' => [
                        'menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 220, 'height' => 220, 'border_radius' => 16],
                    ],
                ],
            ],
        ]);

        // Customer B preference is completely isolated
        $prefB = WorkspacePreference::where('user_id', $customerB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('menu_sections_order', $matrixB);
        $this->assertArrayNotHasKey('menu_items_order', $matrixB);

        // Admin preference is also completely isolated
        $prefAdmin = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrixAdmin = $prefAdmin?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('menu_sections_order', $matrixAdmin);

        // Both Customer B and Admin can load /menus with clean default layout
        $this->actingAs($customerB)->get('/menus')->assertStatus(200);
        $this->actingAs($admin)->get('/menus')->assertStatus(200);
    }

    public function test_menus_layout_page_isolation(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        // Seed preference with other pages layouts
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'  => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'    => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'kitchen_kpi_order'    => ['siap_kirim', 'sedang_dimasak', 'perlu_dimasak', 'total_porsi'],
            'delivery_kpi_order'   => ['terkirim', 'dalam_perjalanan', 'menunggu_kurir', 'total_pengiriman'],
            'profile_sidebar_order'=> ['P-AVATAR', 'P-ACTIONS', 'P-STATS'],
            'component_styles'     => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
            ],
        ];
        $pref->save();

        // Customer saves menus layout
        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'menu_sections_order' => ['M-TOOLBAR', 'M-HEADER', 'M-GRID'],
                'menu_items_order'    => [$fixtures['menu2']->id, $fixtures['menu1']->id, $fixtures['menu3']->id],
                'component_styles'    => [
                    'menus' => [
                        'M-HEADER' => ['shape' => 'rounded', 'width' => 880, 'height' => 130, 'border_radius' => 24],
                        'menu_item_' . $fixtures['menu2']->id => ['shape' => 'pill', 'width' => 320, 'height' => 260, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Check that other pages data remain untouched
        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['siap_kirim', 'sedang_dimasak', 'perlu_dimasak', 'total_porsi'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['terkirim', 'dalam_perjalanan', 'menunggu_kurir', 'total_pengiriman'], $matrix['delivery_kpi_order']);
        $this->assertEquals(['P-AVATAR', 'P-ACTIONS', 'P-STATS'], $matrix['profile_sidebar_order']);

        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['menus']['M-HEADER']['shape']);
        $this->assertEquals('pill', $matrix['component_styles']['menus']['menu_item_' . $fixtures['menu2']->id]['shape']);
    }

    public function test_menus_layout_can_be_reset_to_default(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        // Customise menus layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'   => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'menu_sections_order'   => ['M-GRID', 'M-TOOLBAR', 'M-HEADER'],
            'menu_items_order'      => [$fixtures['menu3']->id, $fixtures['menu1']->id],
            'menu_categories_order' => ['snack-box', 'nasi-box'],
            'component_styles'      => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'menus'     => [
                    'M-HEADER' => ['shape' => 'sharp', 'width' => 800, 'height' => 100, 'border_radius' => 0],
                    'menu_item_' . $fixtures['menu3']->id => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 16],
                ],
            ],
        ];
        $pref->save();

        // Reset menus
        $response = $this->actingAs($customer)->postJson(route('workspace.reset-layout'), [
            'page' => 'menus',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Menus items should be reset
        $this->assertEquals(['M-HEADER', 'M-TOOLBAR', 'M-GRID'], $matrix['menu_sections_order']);
        $this->assertEmpty($matrix['menu_items_order']);
        $this->assertEmpty($matrix['menu_categories_order']);

        // Dashboard remains untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_menu_search_functionality_remains_active_and_safe(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        // Search for "Ayam"
        $response = $this->actingAs($customer)->get('/menus?search=Ayam');
        $response->assertStatus(200);
        $response->assertSee('Nasi Ayam Bakar', false);
        $response->assertDontSee('Paket Prasmanan Mewah', false);
        $response->assertDontSee('Snack Box Manis', false);

        // Search for "resepsi" (description search)
        $responseDesc = $this->actingAs($customer)->get('/menus?search=resepsi');
        $responseDesc->assertStatus(200);
        $responseDesc->assertSee('Paket Prasmanan Mewah', false);
        $responseDesc->assertDontSee('Nasi Ayam Bakar', false);
    }

    public function test_menu_category_filter_remains_active_and_safe(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        // Filter by category slug "snack-box"
        $response = $this->actingAs($customer)->get('/menus?category=snack-box');
        $response->assertStatus(200);
        $response->assertSee('Snack Box Manis', false);
        $response->assertDontSee('Nasi Ayam Bakar', false);
        $response->assertDontSee('Paket Prasmanan Mewah', false);
    }

    public function test_add_to_cart_action_remains_fully_functional_and_unaltered(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createMenuFixtures();

        // Add menu1 to cart
        $response = $this->actingAs($customer)->post(route('cart.add'), [
            'menu_id'  => $fixtures['menu1']->id,
            'quantity' => 2,
        ]);

        // Assert redirect or successful session cart
        $response->assertSessionHasNoErrors();

        // Assert cart item was stored in database
        $cart = Cart::where('user_id', $customer->id)->first();
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id'  => $cart->id,
            'menu_id'  => $fixtures['menu1']->id,
            'quantity' => 2,
        ]);
    }
}