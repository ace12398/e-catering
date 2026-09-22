<?php
namespace App\Http\Controllers;

use App\Models\WorkspacePreference;
use App\Services\AdaptiveEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        protected AdaptiveEngine $adaptiveEngine
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load(['profile', 'workspacePreference']);
        if ($user->workspacePreference?->onboarding_complete) {
            return redirect()->route('dashboard');
        }
        return view('onboarding.wizard', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal' => 'required|in:ordering,managing,both',
            'preset' => 'required|string',
            'density' => 'required|in:compact,comfortable,spacious',
        ]);

        $user = $request->user();
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->update([
            'preset' => $validated['preset'],
            'density' => $validated['density'],
            'goal' => $validated['goal'],
            'onboarding_complete' => true,
        ]);

        $this->adaptiveEngine->clearCache($user->id);

        return redirect()->route('dashboard')->with('status', 'onboarding-complete');
    }
}
