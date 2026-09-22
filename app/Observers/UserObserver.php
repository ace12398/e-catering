<?php

namespace App\Observers;

use App\Events\Profile\ProfileCreated;
use App\Models\Profile;
use App\Models\User;
use App\Models\WorkspacePreference;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        try {
            $profile = Profile::create([
                'user_id' => $user->id,
                'company_name' => null,
                'phone_number' => null,
                'address' => null,
                'completion_percentage' => 0,
            ]);

            WorkspacePreference::create([
                'user_id' => $user->id,
                'preset' => 'institution_organization',
                'layout_matrix' => [],
                'onboarding_complete' => false,
                'density' => 'comfortable',
            ]);

            if (method_exists($user, 'assignRole') && ! $user->hasAnyRole()) {
                $user->assignRole('Customer');
            }

            event(new ProfileCreated($profile));
        } catch (\Throwable $e) {
            Log::error('UserObserver creation error', ['error' => $e->getMessage()]);
        }
    }
}
