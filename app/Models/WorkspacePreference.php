<?php
namespace App\Models;

use App\Enums\WorkspacePreset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspacePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'preset', 'layout_matrix', 'auto_save',
        'onboarding_complete', 'density', 'goal',
    ];

    protected $casts = [
        'preset' => WorkspacePreset::class,
        'layout_matrix' => 'array',
        'auto_save' => 'boolean',
        'onboarding_complete' => 'boolean',
    ];

    public static function getDefaultLayoutMatrix(): array
    {
        return [
            // Dashboard widgets (existing, tidak berubah)
            'dashboard_widgets' => [
                'top'   => ['W-KPI', 'W-QUICK'],
                'left'  => ['W-ORDERS'],
                'right' => ['W-STATUS', 'W-ACTIVITY'],
            ],
            'quick_actions_order' => [
                'catalog', 'cart', 'orders', 'kitchen', 'delivery', 'finance', 'analytics', 'profile'
            ],
            // Dashboard KPI card order
            'dashboard_kpi_order' => ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'],
            // Analytics BI widgets
            'analytics_widgets' => [
                'top'   => ['A-KPI'],
                'left'  => ['A-CHARTS'],
                'right' => ['A-TOP-MENU', 'A-ACTIVITY'],
                'bottom'=> ['A-SYSTEM'],
            ],
            // Analytics KPI card order & Panels order
            'analytics_kpi_order' => ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'],
            'analytics_panels_order' => ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'],
            // Finance KPI card order
            'finance_kpi_order' => ['lunas', 'menunggu', 'belum_bayar', 'ditolak'],
            // Kitchen KPI card order & Kanban panels order
            'kitchen_kpi_order' => ['waiting', 'cooking', 'packing', 'done'],
            'kitchen_panels_order' => ['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'],
            // Delivery KPI card order & delivery items order
            'delivery_kpi_order' => ['waiting', 'on_delivery', 'done', 'total'],
            'delivery_items_order' => [],
            // Menu sections order (Header, Toolbar, Grid)
            'menu_sections_order' => ['M-HEADER', 'M-TOOLBAR', 'M-GRID'],
            // Menu categories order (empty = DB order default)
            'menu_categories_order' => [],
            // Menu items order (empty = DB order default)
            'menu_items_order' => [],
            // Orders sections order, KPI card order & orders items order
            'orders_sections_order' => ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'],
            'orders_kpi_order' => ['pending', 'preparing', 'on_delivery', 'completed'],
            'orders_items_order' => [],
            // Profile sidebar cards order
            'profile_sidebar_order' => ['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'],
            // Cart sections order (Header, Items, Guide, Summary)
            'cart_sections_order' => ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'],
            // =====================================================
            // COMPONENT STYLES — Shape + Size per komponen per halaman
            // shape: rectangle | rounded | sharp | pill | circle | hexagon
            // width, height: dalam pixel (integer)
            // border_radius: dalam pixel (integer, untuk shape=rectangle custom rounding)
            // =====================================================
            'component_styles' => [
                'dashboard' => [
                    'W-KPI-01'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'W-KPI-02'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'W-KPI-03'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'W-KPI-04'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'W-QUICK'    => ['shape' => 'rectangle', 'width' => 900, 'height' => 160, 'border_radius' => 16],
                    'W-ORDERS'   => ['shape' => 'rectangle', 'width' => 900, 'height' => 320, 'border_radius' => 16],
                    'W-STATUS'   => ['shape' => 'rectangle', 'width' => 440, 'height' => 200, 'border_radius' => 16],
                    'W-ACTIVITY' => ['shape' => 'rectangle', 'width' => 440, 'height' => 240, 'border_radius' => 16],
                ],
                'analytics' => [
                    'A-KPI-01'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-KPI-02'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-KPI-03'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-KPI-04'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-KPI-05'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-KPI-06'             => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
                    'A-CHART-ORDERS'       => ['shape' => 'rectangle', 'width' => 440, 'height' => 340, 'border_radius' => 16],
                    'A-CHART-REVENUE'      => ['shape' => 'rectangle', 'width' => 440, 'height' => 340, 'border_radius' => 16],
                    'A-TOP-MENU'           => ['shape' => 'rectangle', 'width' => 560, 'height' => 320, 'border_radius' => 16],
                    'A-ORDER-DISTRIBUTION' => ['shape' => 'rectangle', 'width' => 320, 'height' => 320, 'border_radius' => 16],
                    'A-ACTIVITY'           => ['shape' => 'rectangle', 'width' => 440, 'height' => 260, 'border_radius' => 16],
                    'A-SYSTEM'             => ['shape' => 'rectangle', 'width' => 440, 'height' => 260, 'border_radius' => 16],
                ],
                'finance' => [
                    'lunas'       => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'menunggu'    => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'belum_bayar' => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'ditolak'     => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                ],
                'kitchen' => [
                    'waiting'        => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'cooking'        => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'packing'        => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'done'           => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'k-list-waiting' => ['shape' => 'rectangle', 'width' => 320, 'height' => 380, 'border_radius' => 16],
                    'k-list-cooking' => ['shape' => 'rectangle', 'width' => 320, 'height' => 380, 'border_radius' => 16],
                    'k-list-packing' => ['shape' => 'rectangle', 'width' => 320, 'height' => 380, 'border_radius' => 16],
                    'k-list-done'    => ['shape' => 'rectangle', 'width' => 320, 'height' => 380, 'border_radius' => 16],
                ],
                'delivery' => [
                    'waiting'     => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'on_delivery' => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'done'        => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'total'       => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                ],
                'menus' => [
                    'category_pills' => ['shape' => 'pill', 'width' => 200, 'height' => 44, 'border_radius' => 9999],
                    'M-HEADER'  => ['shape' => 'rectangle', 'width' => 900, 'height' => 120, 'border_radius' => 16],
                    'M-TOOLBAR' => ['shape' => 'rectangle', 'width' => 900, 'height' => 80,  'border_radius' => 16],
                    'M-GRID'    => ['shape' => 'rectangle', 'width' => 900, 'height' => 450, 'border_radius' => 16],
                ],
                'orders' => [
                    'O-HEADER'     => ['shape' => 'rectangle', 'width' => 900, 'height' => 120, 'border_radius' => 16],
                    'O-KPI'        => ['shape' => 'rectangle', 'width' => 900, 'height' => 140, 'border_radius' => 16],
                    'pending'      => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'preparing'    => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'on_delivery'  => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'completed'    => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 12],
                    'O-FILTER'     => ['shape' => 'rectangle', 'width' => 900, 'height' => 80,  'border_radius' => 12],
                    'O-LIST'       => ['shape' => 'rectangle', 'width' => 900, 'height' => 450, 'border_radius' => 16],
                    'O-PAGINATION' => ['shape' => 'rectangle', 'width' => 900, 'height' => 60,  'border_radius' => 12],
                ],
                'profile' => [
                    'P-HEADER'     => ['shape' => 'rectangle', 'width' => 900, 'height' => 120, 'border_radius' => 16],
                    'P-FORM'       => ['shape' => 'rectangle', 'width' => 600, 'height' => 420, 'border_radius' => 16],
                    'P-COMPLETION' => ['shape' => 'rectangle', 'width' => 320, 'height' => 240, 'border_radius' => 16],
                    'P-WORKSPACE'  => ['shape' => 'rectangle', 'width' => 320, 'height' => 160, 'border_radius' => 16],
                    'P-ACTIVITY'   => ['shape' => 'rectangle', 'width' => 320, 'height' => 200, 'border_radius' => 16],
                ],
                'cart' => [
                    'C-CART-HEADER'  => ['shape' => 'rectangle', 'width' => 900, 'height' => 80,  'border_radius' => 16],
                    'C-CART-ITEMS'   => ['shape' => 'rectangle', 'width' => 600, 'height' => 300, 'border_radius' => 16],
                    'C-CART-GUIDE'   => ['shape' => 'rectangle', 'width' => 600, 'height' => 140, 'border_radius' => 16],
                    'C-CART-SUMMARY' => ['shape' => 'rectangle', 'width' => 320, 'height' => 450, 'border_radius' => 16],
                ],
            ],
        ];
    }

    public function getEffectiveLayoutMatrixAttribute(): array
    {
        $matrix   = $this->layout_matrix;
        $defaults = static::getDefaultLayoutMatrix();

        if (empty($matrix) || !is_array($matrix)) {
            return $defaults;
        }

        // Merge per-key agar data lama tetap valid dan key baru terisi default
        return array_merge($defaults, $matrix);
    }

    /**
     * Mengembalikan daftar shape yang sah dan kompatibel untuk komponen tertentu (Content Integrity Rules).
     */
    public static function getCompatibleShapes(string $componentId): array
    {
        // Table components that strictly reject circle & hexagon clip paths to prevent content clipping
        if ($componentId === 'W-ORDERS') {
            return ['rectangle', 'rounded', 'sharp', 'pill'];
        }

        // Toolbar items reject circle & hexagon
        if ($componentId === 'W-QUICK') {
            return ['rectangle', 'rounded', 'sharp', 'pill'];
        }

        // Menu category pills
        if ($componentId === 'category_pills') {
            return ['pill', 'rounded', 'sharp', 'rectangle'];
        }

        // All Analytics, Finance, Kitchen, Delivery components allow all 6 shapes
        return ['rectangle', 'rounded', 'sharp', 'pill', 'circle', 'hexagon'];
    }

    public function getActivePresetAttribute(): string
    {
        if (is_object($this->preset) && property_exists($this->preset, 'value')) {
            return $this->preset->value;
        }
        return (string) ($this->preset ?? 'institution_organization');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

