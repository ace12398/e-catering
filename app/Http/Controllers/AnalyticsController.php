<?php

namespace App\Http\Controllers;

use App\Models\WorkspacePreference;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $analyticsData = $this->analyticsService->getAnalyticsData();

        $preference = WorkspacePreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['preset' => 'institution_organization']
        );

        $layoutMatrix          = $preference->effective_layout_matrix;
        $defaultMatrix         = WorkspacePreference::getDefaultLayoutMatrix();
        $analyticsStyles       = $layoutMatrix['component_styles']['analytics'] ?? $defaultMatrix['component_styles']['analytics'];
        $analyticsKpiOrder     = $layoutMatrix['analytics_kpi_order'] ?? ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'];
        $analyticsPanelsOrder  = $layoutMatrix['analytics_panels_order'] ?? ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'];

        return view('analytics.dashboard', array_merge($analyticsData, [
            'layout_matrix'        => $layoutMatrix,
            'preference'           => $preference,
            'analyticsStyles'      => $analyticsStyles,
            'analyticsKpiOrder'    => $analyticsKpiOrder,
            'analyticsPanelsOrder' => $analyticsPanelsOrder,
        ]));
    }
}
