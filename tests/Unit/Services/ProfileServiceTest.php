<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_updates_user_and_profile_data(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $service = app(ProfileService::class);

        $updatedUser = $service->updateProfile($user, [
            'name' => 'Updated Name',
            'company_name' => 'CaterCorp',
            'phone_number' => '0811111111',
            'address' => 'Sudirman Central',
        ]);

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('CaterCorp', $updatedUser->profile->company_name);
    }
}
