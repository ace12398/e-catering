<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\WorkspacePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerProfileAdaptiveLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(string $name = 'Customer User', array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name'     => $name,
            'role'     => 'customer',
            'username' => 'cust_' . uniqid(),
        ], $attributes));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'PT Mandiri Sejahtera',
                'phone_number' => '081234567890',
                'address'      => 'Jl. Sudirman No. 25, Jakarta Pusat',
            ]
        );

        WorkspacePreference::updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_complete' => true]
        );

        return $user;
    }

    public function test_customer_can_access_profile_workspace(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Informasi Profil Pengguna', false);
        $response->assertSee('Mode Penyesuaian Tampilan Aktif — Profil Saya', false);
        $response->assertSee('⚙️ Sesuaikan Tampilan', false);
        $response->assertSee('Simpan Perubahan Profil', false);
    }

    public function test_profile_renders_default_components_and_sidebar_order(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get('/profile');

        $response->assertStatus(200);

        // Header and Main Form
        $response->assertSee('id="profile-wrapper-P-HEADER"', false);
        $response->assertSee('id="profile-wrapper-P-FORM"', false);

        // Sidebar components
        $response->assertSee('data-profile-card="P-COMPLETION"', false);
        $response->assertSee('data-profile-card="P-WORKSPACE"', false);
        $response->assertSee('data-profile-card="P-ACTIVITY"', false);

        // Drag handle
        $response->assertSee('profile-sidebar-drag-handle', false);
    }

    public function test_customer_can_save_custom_profile_sidebar_order_and_styles(): void
    {
        $customer = $this->createCustomer();

        $customSidebarOrder = ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'];
        $customStyles = [
            'P-HEADER'     => ['shape' => 'rounded', 'width' => 880, 'height' => 140, 'border_radius' => 24],
            'P-FORM'       => ['shape' => 'sharp', 'width' => 620, 'height' => 460, 'border_radius' => 0],
            'P-COMPLETION' => ['shape' => 'pill', 'width' => 310, 'height' => 210, 'border_radius' => 9999],
            'P-WORKSPACE'  => ['shape' => 'hexagon', 'width' => 330, 'height' => 170, 'border_radius' => 0],
        ];

        $response = $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => $customSidebarOrder,
                'component_styles'      => [
                    'profile' => $customStyles,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $savedMatrix = $pref->effective_layout_matrix;

        $this->assertEquals($customSidebarOrder, $savedMatrix['profile_sidebar_order']);
        $this->assertEquals('rounded', $savedMatrix['component_styles']['profile']['P-HEADER']['shape']);
        $this->assertEquals(880, $savedMatrix['component_styles']['profile']['P-HEADER']['width']);
        $this->assertEquals('sharp', $savedMatrix['component_styles']['profile']['P-FORM']['shape']);
        $this->assertEquals('pill', $savedMatrix['component_styles']['profile']['P-COMPLETION']['shape']);
        $this->assertEquals('hexagon', $savedMatrix['component_styles']['profile']['P-WORKSPACE']['shape']);
    }

    public function test_saved_profile_sidebar_order_and_styles_persist_on_page_load(): void
    {
        $customer = $this->createCustomer();

        $customSidebarOrder = ['P-ACTIVITY', 'P-COMPLETION', 'P-WORKSPACE'];

        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => $customSidebarOrder,
            ],
        ]);

        $customer->unsetRelation('preference');
        $response = $this->actingAs($customer)->get('/profile');

        $response->assertStatus(200);
        $content = $response->getContent();

        $posActivity   = strpos($content, 'data-profile-card="P-ACTIVITY"');
        $posCompletion = strpos($content, 'data-profile-card="P-COMPLETION"');
        $posWorkspace  = strpos($content, 'data-profile-card="P-WORKSPACE"');

        $this->assertTrue($posActivity < $posCompletion, 'P-ACTIVITY must render before P-COMPLETION');
        $this->assertTrue($posCompletion < $posWorkspace, 'P-COMPLETION must render before P-WORKSPACE');
    }

    public function test_customer_profile_layout_user_isolation(): void
    {
        $customerA = $this->createCustomer('Customer A');
        $customerB = $this->createCustomer('Customer B');

        $orderA = ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'];
        $orderB = ['P-WORKSPACE', 'P-COMPLETION', 'P-ACTIVITY'];

        $this->actingAs($customerA)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => ['profile_sidebar_order' => $orderA],
        ]);

        $this->actingAs($customerB)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => ['profile_sidebar_order' => $orderB],
        ]);

        $customerA->unsetRelation('preference');
        $customerB->unsetRelation('preference');

        $prefA = WorkspacePreference::where('user_id', $customerA->id)->first();
        $prefB = WorkspacePreference::where('user_id', $customerB->id)->first();

        $this->assertEquals($orderA, $prefA->effective_layout_matrix['profile_sidebar_order']);
        $this->assertEquals($orderB, $prefB->effective_layout_matrix['profile_sidebar_order']);
    }

    public function test_profile_layout_page_isolation(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
                'component_styles'      => [
                    'profile' => [
                        'P-HEADER' => ['shape' => 'hexagon', 'width' => 820, 'height' => 150, 'border_radius' => 0],
                    ],
                ],
            ],
        ]);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $pref->effective_layout_matrix;

        // Verify other pages remained unchanged
        $this->assertEquals(['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'], $matrix['cart_sections_order']);
        $this->assertEquals(['M-HEADER', 'M-TOOLBAR', 'M-GRID'], $matrix['menu_sections_order']);
        $this->assertEquals(['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'], $matrix['orders_sections_order']);
        $this->assertEquals('rectangle', $matrix['component_styles']['menus']['M-HEADER']['shape']);
        $this->assertEquals('rectangle', $matrix['component_styles']['cart']['C-CART-HEADER']['shape']);
        $this->assertEquals('rectangle', $matrix['component_styles']['orders']['O-HEADER']['shape']);
    }

    public function test_profile_layout_can_be_reset_to_default_without_altering_customer_data(): void
    {
        $customer = $this->createCustomer('Customer Test Reset');

        // Customize profile layout first
        $this->actingAs($customer)->postJson(route('workspace.save-layout'), [
            'layout_matrix' => [
                'profile_sidebar_order' => ['P-ACTIVITY', 'P-WORKSPACE', 'P-COMPLETION'],
                'component_styles'      => [
                    'profile' => [
                        'P-HEADER' => ['shape' => 'pill', 'width' => 700, 'height' => 100, 'border_radius' => 9999],
                    ],
                ],
            ],
        ]);

        // Reset profile layout
        $response = $this->actingAs($customer)->postJson(route('workspace.reset-layout'), [
            'page' => 'profile',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $customer->unsetRelation('preference');
        $pref = WorkspacePreference::where('user_id', $customer->id)->first();
        $matrix = $pref->effective_layout_matrix;

        $defaultMatrix = WorkspacePreference::getDefaultLayoutMatrix();
        $this->assertEquals($defaultMatrix['profile_sidebar_order'], $matrix['profile_sidebar_order']);
        $this->assertEquals('rectangle', $matrix['component_styles']['profile']['P-HEADER']['shape']);

        // Profile business data must remain intact
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Customer Test Reset']);
        $this->assertDatabaseHas('profiles', [
            'user_id'      => $customer->id,
            'company_name' => 'PT Mandiri Sejahtera',
            'phone_number' => '081234567890',
            'address'      => 'Jl. Sudirman No. 25, Jakarta Pusat',
        ]);
    }

    public function test_customer_can_update_profile_data_safely(): void
    {
        $customer = $this->createCustomer('Original Name');

        $response = $this->actingAs($customer)->patch(route('profile.update'), [
            'name'         => 'Nama Baru Customer',
            'company_name' => 'PT Makmur Sentosa',
            'phone_number' => '081122334455',
            'address'      => 'Jl. Gatot Subroto Kav 18, Jakarta',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Informasi profil berhasil diperbarui!');

        $this->assertDatabaseHas('users', [
            'id'   => $customer->id,
            'name' => 'Nama Baru Customer',
        ]);
        $this->assertDatabaseHas('profiles', [
            'user_id'      => $customer->id,
            'company_name' => 'PT Makmur Sentosa',
            'phone_number' => '081122334455',
            'address'      => 'Jl. Gatot Subroto Kav 18, Jakarta',
        ]);
    }

    public function test_customer_avatar_upload_and_delete_workflow(): void
    {
        Storage::fake('public');

        $customer = $this->createCustomer();
        $fakeImage = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        // Upload Avatar
        $responseUpload = $this->actingAs($customer)->post(route('profile.avatar'), [
            'avatar' => $fakeImage,
        ]);

        $responseUpload->assertRedirect();
        $responseUpload->assertSessionHas('success', 'Foto profil berhasil diunggah!');

        $profile = Profile::where('user_id', $customer->id)->first();
        $this->assertNotNull($profile->avatar);

        // Delete Avatar
        $responseDelete = $this->actingAs($customer)->delete(route('profile.delete-avatar'));

        $responseDelete->assertRedirect();
        $responseDelete->assertSessionHas('success', 'Foto profil berhasil dihapus!');

        $profileFresh = Profile::where('user_id', $customer->id)->first();
        $this->assertNull($profileFresh->avatar);
    }

    public function test_profile_form_inputs_and_actions_remain_accessible_and_unaltered(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get('/profile');

        $response->assertStatus(200);

        // Form elements
        $response->assertSee('name="name"', false);
        $response->assertSee('name="company_name"', false);
        $response->assertSee('name="phone_number"', false);
        $response->assertSee('name="address"', false);
        $response->assertSee('name="avatar"', false);
        $response->assertSee('type="submit"', false);
    }
}
