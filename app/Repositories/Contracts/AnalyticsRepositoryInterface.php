<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    public function getSummaryMetrics(): array;
    public function getAnalyticsData(): array;
}
