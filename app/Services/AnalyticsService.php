<?php

namespace App\Services;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;

class AnalyticsService
{
    public function __construct(
        protected AnalyticsRepositoryInterface $analyticsRepo
    ) {}

    public function getSummaryMetrics(): array
    {
        return $this->analyticsRepo->getSummaryMetrics();
    }

    public function getAnalyticsData(): array
    {
        return $this->analyticsRepo->getAnalyticsData();
    }
}
