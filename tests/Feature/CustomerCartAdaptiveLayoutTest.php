<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Menu;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCartAdaptiveLayoutTest extends TestCase
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

    protected function createCartFixtures(User $customer): array
    {
        $unique = uniqid();
        $vendor = Vendor::create([
            'name' => 'Dapur Utama ' . $unique,
            'slug' => 'dapur-utama-' . $unique,
            'phone' => '08123456789',
            'rating' => 4.8,
            'is_active' => true,
        ]);

        $cat1 = Category::create(['name' => 'Nasi Box', 'slug' => 'nasi-box-' . $unique, 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Prasmanan', 'slug' => 'prasmanan-' . $unique, 'is_active' => true]);

        $menu1 = Menu::create([
            'name' => 'Nasi Ayam Bakar',
            'slug' => 'nasi-ayam-bakar-' . $unique,
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
            'slug' => 'paket-prasmanan-mewah-' . $unique,
            'description' => 'Menu lengkap prasmanan untuk resepsi',
            'price' => 75000,
            'category_id' => $cat2->id,
            'vendor_id' => $vendor->id,
            'calories' => 850,
            'is_available' => true,
            'is_halal' => true,
        ]);

        $pm1 = PaymentMethod::firstOrCreate(
            ['code' => 'qris'],
            [
                'name' => 'QRIS GoPay / OVO',
                'type' => 'digital',
                'is_active' => true,
            ]
        );

        $pm2 = PaymentMethod::firstOrCreate(
            ['code' => 'bca_va'],
            [
                'name' => 'Transfer Bank BCA',
                'type' => 'transfer',
                'is_active' => true,
            ]
        );

        $cart = Cart::firstOrCreate([
            'user_id' => $customer->id,
        ]);

        $item1 = CartItem::create([
            'cart_id'    => $cart->id,
            'menu_id'    => $menu1->id,
            'quantity'   => 2,
            'unit_price' => 35000,
            'notes'      => 'Pedas sedang',
        ]);

        $item2 = CartItem::create([
            'cart_id'    => $cart->id,
            'menu_id'    => $menu2->id,
            'quantity'   => 1,
            'unit_price' => 75000,
            'notes'      => 'Sertakan sendok ekstra',
        ]);

        return compact('vendor', 'cat1', 'cat2', 'menu1', 'menu2', 'pm1', 'pm2', 'cart', 'item1', 'item2');
    }

    public function test_customer_can_access_cart_workspace(): void
    {
        $customer = $this->createCustomer();
        $this->createCartFixtures($customer);

        $response = $this->actingAs($customer)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja Katering', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif', false);
        $response->assertSee('data-cart-section="C-CART-HEADER"', false);
        $response->assertSee('data-cart-section="C-CART-ITEMS"', false);
        $response->assertSee('data-cart-section="C-CART-GUIDE"', false);
        $response->assertSee('data-cart-section="C-CART-SUMMARY"', false);
        $response->assertSee('cart-section-drag-handle', false);
    }

    public function test_cart_renders_default_sections_order_and_items(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createCartFixtures($customer);

        $response = $this->actingAs($customer)->get('/cart');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Check default section order: C-CART-HEADER before C-CART-ITEMS before C-CART-GUIDE before C-CART-SUMMARY
        $posHeader  = strpos($content, 'data-cart-section="C-CART-HEADER"');
        $posItems   = strpos($content, 'data-cart-section="C-CART-ITEMS"');
        $posGuide   = strpos($content, 'data-cart-section="C-CART-GUIDE"');
        $posSummary = strpos($content, 'data-cart-section="C-CART-SUMMARY"');

        $this->assertTrue($posHeader !== false && $posItems !== false && $posGuide !== false && $posSummary !== false);
        $this->assertTrue($posHeader < $posItems && $posItems < $posGuide && $posGuide < $posSummary, 'Sections should render in default order');

        // Check items and subtotal exist
        $response->assertSee('Nasi Ayam Bakar', false);
        $response->assertSee('Paket Prasmanan Mewah', false);
        $response->assertSee('Pedas sedang', false);
    }

    public function test_customer_can_save_custom_cart_sections_order_and_styles(): void
    {
        $customer = $this->createCustomer();
        $this->createCartFixtures($customer);

        $customSectionOrder = ['C-CART-SUMMARY', 'C-CART-ITEMS', 'C-CART-HEADER', 'C-CART-GUIDE'];
        $customStyles       = [
            'C-CART-HEADER'  => ['shape' => 'rounded', 'width' => 880, 'height' => 100, 'border_radius' => 24],
            'C-CART-ITEMS'   => ['shape' => 'sharp', 'width' => 640, 'height' => 320, 'border_radius' => 0],
            'C-CART-GUIDE'   => ['shape' => 'pill', 'width' => 580, 'height' => 130, 'border_radius' => 9999],
            'C-CART-SUMMARY' => ['shape' => 'hexagon', 'width' => 360, 'height' => 480, 'border_radius' => 16],
        ];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'cart_sections_order' => $customSectionOrder,
                'component_styles'    => [
                    'cart' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $this->assertNotNull($pref);
        $matrix = $pref->layout_matrix;

        $this->assertEquals($customSectionOrder, $matrix['cart_sections_order']);
        $this->assertEquals('rounded', $matrix['component_styles']['cart']['C-CART-HEADER']['shape']);
        $this->assertEquals('hexagon', $matrix['component_styles']['cart']['C-CART-SUMMARY']['shape']);
    }

    public function test_saved_cart_sections_order_and_styles_persist_on_page_load(): void
    {
        $customer = $this->createCustomer();
        $this->createCartFixtures($customer);

        $customSectionOrder = ['C-CART-SUMMARY', 'C-CART-ITEMS', 'C-CART-HEADER', 'C-CART-GUIDE'];

        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $matrix = $pref->effective_layout_matrix;
        $matrix['cart_sections_order'] = $customSectionOrder;
        $matrix['component_styles']['cart'] = [
            'C-CART-HEADER'  => ['shape' => 'rounded', 'width' => 850, 'height' => 90, 'border_radius' => 24],
            'C-CART-SUMMARY' => ['shape' => 'circle', 'width' => 340, 'height' => 340, 'border_radius' => 16],
        ];
        $pref->layout_matrix = $matrix;
        $pref->save();

        $customer->unsetRelation('preference');

        $response = $this->actingAs($customer)->get('/cart');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verify custom sections order in DOM (C-CART-SUMMARY -> C-CART-ITEMS -> C-CART-HEADER -> C-CART-GUIDE)
        $posSummary = strpos($content, 'data-cart-section="C-CART-SUMMARY"');
        $posItems   = strpos($content, 'data-cart-section="C-CART-ITEMS"');
        $posHeader  = strpos($content, 'data-cart-section="C-CART-HEADER"');
        $posGuide   = strpos($content, 'data-cart-section="C-CART-GUIDE"');

        $this->assertTrue($posSummary < $posItems && $posItems < $posHeader && $posHeader < $posGuide, 'Sections must render in saved custom order');
    }

    public function test_customer_cart_layout_user_isolation(): void
    {
        $customerA = $this->createCustomer();
        $customerB = $this->createCustomer();
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'adm_' . uniqid()]);
        $this->createCartFixtures($customerA);
        $this->createCartFixtures($customerB);

        // Customer A saves custom section order
        $customSectionOrderA = ['C-CART-GUIDE', 'C-CART-SUMMARY', 'C-CART-ITEMS', 'C-CART-HEADER'];
        $this->actingAs($customerA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'cart_sections_order' => $customSectionOrderA,
                'component_styles'    => [
                    'cart' => [
                        'C-CART-GUIDE' => ['shape' => 'pill', 'width' => 500, 'height' => 120, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Customer B preference is completely isolated
        $prefB = WorkspacePreference::where('user_id', $customerB->id)->first();
        $matrixB = $prefB?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('cart_sections_order', $matrixB);

        // Admin preference is also completely isolated
        $prefAdmin = WorkspacePreference::where('user_id', $admin->id)->first();
        $matrixAdmin = $prefAdmin?->layout_matrix ?? [];
        $this->assertArrayNotHasKey('cart_sections_order', $matrixAdmin);

        // Both Customer B and Customer A can load /cart cleanly
        $this->actingAs($customerB)->get('/cart')->assertStatus(200);
        $this->actingAs($customerA)->get('/cart')->assertStatus(200);
    }

    public function test_cart_layout_page_isolation(): void
    {
        $customer = $this->createCustomer();
        $this->createCartFixtures($customer);

        // Seed preference with other pages layouts
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order'  => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'finance_kpi_order'    => ['ditolak', 'belum_bayar', 'menunggu', 'lunas'],
            'menu_sections_order'  => ['M-TOOLBAR', 'M-GRID', 'M-HEADER'],
            'kitchen_kpi_order'    => ['siap_kirim', 'sedang_dimasak', 'perlu_dimasak', 'total_porsi'],
            'delivery_kpi_order'   => ['terkirim', 'dalam_perjalanan', 'menunggu_kurir', 'total_pengiriman'],
            'profile_sidebar_order'=> ['P-AVATAR', 'P-ACTIONS', 'P-STATS'],
            'component_styles'     => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'finance'   => ['ditolak'  => ['shape' => 'circle', 'width' => 200, 'height' => 200, 'border_radius' => 12]],
            ],
        ];
        $pref->save();

        // Customer saves cart layout
        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'cart_sections_order' => ['C-CART-SUMMARY', 'C-CART-ITEMS', 'C-CART-HEADER', 'C-CART-GUIDE'],
                'component_styles'    => [
                    'cart' => [
                        'C-CART-HEADER' => ['shape' => 'rounded', 'width' => 880, 'height' => 100, 'border_radius' => 24],
                    ],
                ],
            ],
        ]);

        // Check that other pages data remain untouched
        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals(['ditolak', 'belum_bayar', 'menunggu', 'lunas'], $matrix['finance_kpi_order']);
        $this->assertEquals(['M-TOOLBAR', 'M-GRID', 'M-HEADER'], $matrix['menu_sections_order']);
        $this->assertEquals(['siap_kirim', 'sedang_dimasak', 'perlu_dimasak', 'total_porsi'], $matrix['kitchen_kpi_order']);
        $this->assertEquals(['terkirim', 'dalam_perjalanan', 'menunggu_kurir', 'total_pengiriman'], $matrix['delivery_kpi_order']);
        $this->assertEquals(['P-AVATAR', 'P-ACTIONS', 'P-STATS'], $matrix['profile_sidebar_order']);

        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
        $this->assertEquals('circle', $matrix['component_styles']['finance']['ditolak']['shape']);
        $this->assertEquals('rounded', $matrix['component_styles']['cart']['C-CART-HEADER']['shape']);
    }

    public function test_cart_layout_can_be_reset_to_default(): void
    {
        $customer = $this->createCustomer();
        $this->createCartFixtures($customer);

        // Customise cart layout
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $customer->id]);
        $pref->layout_matrix = [
            'dashboard_kpi_order' => ['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'],
            'cart_sections_order' => ['C-CART-GUIDE', 'C-CART-SUMMARY', 'C-CART-ITEMS', 'C-CART-HEADER'],
            'component_styles'    => [
                'dashboard' => ['W-KPI-04' => ['shape' => 'hexagon', 'width' => 280, 'height' => 140, 'border_radius' => 16]],
                'cart'      => [
                    'C-CART-HEADER' => ['shape' => 'sharp', 'width' => 800, 'height' => 100, 'border_radius' => 0],
                ],
            ],
        ];
        $pref->save();

        // Reset cart
        $response = $this->actingAs($customer)->postJson(route('workspace.reset-layout'), [
            'page' => 'cart',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $prefFresh = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $prefFresh->layout_matrix;

        // Cart items should be reset
        $this->assertEquals(['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'], $matrix['cart_sections_order']);

        // Dashboard remains untouched
        $this->assertEquals(['W-KPI-04', 'W-KPI-03', 'W-KPI-02', 'W-KPI-01'], $matrix['dashboard_kpi_order']);
        $this->assertEquals('hexagon', $matrix['component_styles']['dashboard']['W-KPI-04']['shape']);
    }

    public function test_cart_quantity_controls_remain_functional(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createCartFixtures($customer);

        // Increment quantity of item 1 from 2 to 3
        $response = $this->actingAs($customer)->patch(route('cart.update', $fixtures['item1']->id), [
            'quantity' => 3,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cart_items', [
            'id'       => $fixtures['item1']->id,
            'quantity' => 3,
        ]);
    }

    public function test_cart_remove_item_remains_functional(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createCartFixtures($customer);

        // Remove item 2 from cart
        $response = $this->actingAs($customer)->delete(route('cart.remove', $fixtures['item2']->id));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('cart_items', [
            'id' => $fixtures['item2']->id,
        ]);
    }

    public function test_cart_order_checkout_submission_remains_functional(): void
    {
        $customer = $this->createCustomer();
        $fixtures = $this->createCartFixtures($customer);

        // Checkout order
        $response = $this->actingAs($customer)->post(route('orders.store'), [
            'delivery_address'  => 'Jl. Merdeka No. 45, Jakarta Selatan',
            'payment_method_id' => $fixtures['pm1']->id,
            'notes'             => 'Harap konfirmasi sebelum antar',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'user_id'          => $customer->id,
            'delivery_address' => 'Jl. Merdeka No. 45, Jakarta Selatan',
            'notes'            => 'Harap konfirmasi sebelum antar',
        ]);
    }
}