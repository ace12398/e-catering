<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteAvatarRequest;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AdaptiveEngine;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService,
        protected AdaptiveEngine $adaptiveEngine
    ) {}

    public function index(Request $request): View
    {
        return $this->edit($request);
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        $preference = \App\Models\WorkspacePreference::firstOrCreate(
            ['user_id' => $user->id],
            ['preset' => 'institution_organization']
        );

        $layoutMatrix        = $preference->effective_layout_matrix;
        $defaultMatrix       = \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $profileStyles       = $layoutMatrix['component_styles']['profile'] ?? $defaultMatrix['component_styles']['profile'];
        $profileSidebarOrder = $layoutMatrix['profile_sidebar_order'] ?? ['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'];

        return view('profile.edit', compact('user', 'preference', 'layoutMatrix', 'profileStyles', 'profileSidebarOrder'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->profileService->updateProfile(
            $user,
            $request->validated(),
            $request->file('avatar')
        );

        $this->adaptiveEngine->clearCache($user->id);

        return redirect()->back()->with('status', 'profile-updated')->with('success', 'Informasi profil berhasil diperbarui!');
    }

    public function uploadAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->profileService->uploadAvatar($user, $request->file('avatar'));

        $this->adaptiveEngine->clearCache($user->id);

        return redirect()->back()->with('status', 'avatar-uploaded')->with('success', 'Foto profil berhasil diunggah!');
    }

    public function deleteAvatar(DeleteAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->profileService->deleteAvatar($user);

        $this->adaptiveEngine->clearCache($user->id);

        return redirect()->back()->with('status', 'avatar-deleted')->with('success', 'Foto profil berhasil dihapus!');
    }
}
