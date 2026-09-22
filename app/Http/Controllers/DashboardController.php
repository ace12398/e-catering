<?php
namespace App\Http\Controllers;

use App\Services\AdaptiveEngine;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AdaptiveEngine $adaptiveEngine
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $ctx = $this->adaptiveEngine->getContext($user);

        if (!$ctx['onboarding_complete'] && $ctx['is_first_login']) {
            return redirect()->route('onboarding.show');
        }

        $data = $this->dashboardService->getDashboardData($user);
        return view('dashboard.index', $data);
    }
}
