<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_accessible_by_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Simpan Perubahan Profil');
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withoutMiddleware()->patch('/profile/update', [
            'name' => 'John Pratama Doe',
            'company_name' => 'CaterFlow Katering',
            'phone_number' => '08123456789',
            'address' => 'Jakarta Barat',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'John Pratama Doe']);
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'company_name' => 'CaterFlow Katering']);
    }
}
