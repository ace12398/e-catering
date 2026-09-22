<?php

namespace App\Services;

use App\Enums\WorkspacePreset;

class WorkspaceService
{
    /**
     * Resolve default layout matrix for a given workspace preset.
     */
    public function getPresetLayout(WorkspacePreset|string $preset): array
    {
        $presetKey = $preset instanceof WorkspacePreset ? $preset->value : $preset;

        return match ($presetKey) {
            'institution_organization' => [
                ['id' => 'WGT-CUS-01', 'name' => 'KPI Stats Summary', 'col' => 8, 'visible' => true, 'pinned' => true],
                ['id' => 'WGT-CUS-02', 'name' => 'Quick Reorder Card', 'col' => 4, 'visible' => true, 'pinned' => false],
                ['id' => 'WGT-CUS-03', 'name' => 'Order History Data Table', 'col' => 12, 'visible' => true, 'pinned' => false],
            ],
            'operations_logistics' => [
                ['id' => 'WGT-CUS-TRK-01', 'name' => 'Live GPS Route Map', 'col' => 8, 'visible' => true, 'pinned' => true],
                ['id' => 'WGT-CUS-TRK-02', 'name' => 'Catering Stepper Timeline', 'col' => 4, 'visible' => true, 'pinned' => false],
            ],
            'finance_audit' => [
                ['id' => 'WGT-CUS-PAY-05', 'name' => 'Tax Invoice Cost Summary', 'col' => 6, 'visible' => true, 'pinned' => true],
                ['id' => 'WGT-CUS-HIS-04', 'name' => 'Monthly Spend Analytics', 'col' => 6, 'visible' => true, 'pinned' => false],
            ],
            default => [
                ['id' => 'WGT-CUS-01', 'name' => 'KPI Stats Summary', 'col' => 12, 'visible' => true, 'pinned' => false],
            ],
        };
    }
}
