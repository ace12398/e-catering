<?php

namespace Tests\Unit\Observers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_triggers_profile_and_workspace_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->profile);
        $this->assertNotNull($user->workspacePreference);
        $this->assertNotNull($user->workspacePreference->preset);
    }
}
