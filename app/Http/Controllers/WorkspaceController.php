<?php
namespace App\Http\Controllers;

use App\Enums\WorkspacePreset;
use App\Models\WorkspacePreference;
use App\Services\AdaptiveEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct(
        protected AdaptiveEngine $adaptiveEngine
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $preference = WorkspacePreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'preset' => WorkspacePreset::INSTITUTION_ORGANIZATION,
                'widget_order' => ['summary_kpi', 'order_status', 'quick_reorder', 'budget_tracker'],
                'layout_density' => 'comfortable',
            ]
        );
        $ctx = $this->adaptiveEngine->getContext($user);
        $layoutMatrix = $preference->effective_layout_matrix;

        // Customization map per halaman & komponen
        $customizationMap = [
            'dashboard' => [
                'name' => 'Dashboard Beranda',
                'components' => [
                    'W-KPI'      => ['label' => 'Ringkasan KPI Utama', 'icon' => '📊', 'default_w' => 320, 'default_h' => 180, 'min_w' => 240, 'max_w' => 800, 'min_h' => 120, 'max_h' => 500],
                    'W-QUICK'    => ['label' => 'Akses Cepat (Toolbar Pintasan)', 'icon' => '⚡', 'default_w' => 320, 'default_h' => 160, 'min_w' => 240, 'max_w' => 800, 'min_h' => 120, 'max_h' => 500],
                    'W-ORDERS'   => ['label' => 'Tabel Pesanan Terbaru', 'icon' => '📦', 'default_w' => 480, 'default_h' => 320, 'min_w' => 320, 'max_w' => 800, 'min_h' => 200, 'max_h' => 600],
                    'W-STATUS'   => ['label' => 'Status Dapur & Kurir', 'icon' => '👨‍🍳', 'default_w' => 320, 'default_h' => 200, 'min_w' => 240, 'max_w' => 800, 'min_h' => 120, 'max_h' => 500],
                    'W-ACTIVITY' => ['label' => 'Timeline Aktivitas Sistem', 'icon' => '🕒', 'default_w' => 320, 'default_h' => 240, 'min_w' => 240, 'max_w' => 800, 'min_h' => 120, 'max_h' => 500],
                ],
            ],
            'analytics' => [
                'name' => 'Analisis Bisnis (BI)',
                'components' => [
                    'A-KPI'      => ['label' => 'Ringkasan BI Utama', 'icon' => '📈', 'default_w' => 900, 'default_h' => 160, 'min_w' => 240, 'max_w' => 900, 'min_h' => 120, 'max_h' => 600],
                    'A-CHARTS'   => ['label' => 'Grafik Pendapatan & Pesanan', 'icon' => '📊', 'default_w' => 480, 'default_h' => 360, 'min_w' => 320, 'max_w' => 900, 'min_h' => 240, 'max_h' => 600],
                    'A-TOP-MENU' => ['label' => 'Tabel Menu Terlaris', 'icon' => '🏆', 'default_w' => 320, 'default_h' => 240, 'min_w' => 280, 'max_w' => 900, 'min_h' => 180, 'max_h' => 600],
                    'A-ACTIVITY' => ['label' => 'Log Aktivitas Sistem', 'icon' => '📝', 'default_w' => 320, 'default_h' => 200, 'min_w' => 240, 'max_w' => 900, 'min_h' => 120, 'max_h' => 600],
                    'A-SYSTEM'   => ['label' => 'Status Server & Health', 'icon' => '🖥️', 'default_w' => 900, 'default_h' => 140, 'min_w' => 240, 'max_w' => 900, 'min_h' => 100, 'max_h' => 400],
                ],
            ],
            'finance' => [
                'name' => 'Keuangan & Invoice',
                'components' => [
                    'lunas'       => ['label' => 'Kartu KPI: Lunas Verifikasi', 'icon' => '✅', 'default_w' => 240, 'default_h' => 120, 'min_w' => 160, 'max_w' => 400, 'min_h' => 80, 'max_h' => 200],
                    'menunggu'    => ['label' => 'Kartu KPI: Menunggu Verifikasi', 'icon' => '⏳', 'default_w' => 240, 'default_h' => 120, 'min_w' => 160, 'max_w' => 400, 'min_h' => 80, 'max_h' => 200],
                    'belum_bayar' => ['label' => 'Kartu KPI: Belum Dibayar', 'icon' => '💳', 'default_w' => 240, 'default_h' => 120, 'min_w' => 160, 'max_w' => 400, 'min_h' => 80, 'max_h' => 200],
                    'ditolak'     => ['label' => 'Kartu KPI: Ditolak / Batal', 'icon' => '❌', 'default_w' => 240, 'default_h' => 120, 'min_w' => 160, 'max_w' => 400, 'min_h' => 80, 'max_h' => 200],
                ],
            ],
            'kitchen' => [
                'name' => 'Manajemen Dapur',
                'components' => [
                    'waiting' => ['label' => 'Kartu Stat: Pesanan Menunggu', 'icon' => '📋', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'cooking' => ['label' => 'Kartu Stat: Sedang Dimasak', 'icon' => '🍳', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'packing' => ['label' => 'Kartu Stat: Proses Packing', 'icon' => '📦', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'done'    => ['label' => 'Kartu Stat: Siap Diantar', 'icon' => '✅', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                ],
            ],
            'delivery' => [
                'name' => 'Pelacakan Pengiriman',
                'components' => [
                    'waiting'     => ['label' => 'Kartu Stat: Menunggu Kurir', 'icon' => '🛵', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'on_delivery' => ['label' => 'Kartu Stat: Dalam Pengiriman', 'icon' => '🚚', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'done'        => ['label' => 'Kartu Stat: Tiba Di Lokasi', 'icon' => '🏡', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                    'total'       => ['label' => 'Kartu Stat: Total Pengiriman', 'icon' => '📦', 'default_w' => 240, 'default_h' => 120, 'min_w' => 140, 'max_w' => 400, 'min_h' => 70, 'max_h' => 180],
                ],
            ],
            'menus' => [
                'name' => 'Katalog Menu',
                'components' => [
                    'category_pills' => ['label' => 'Tombol Filter Kategori', 'icon' => '🏷️', 'default_w' => 200, 'default_h' => 44, 'min_w' => 120, 'max_w' => 400, 'min_h' => 36, 'max_h' => 80],
                ],
            ],
        ];

        return view('workspace.customize', compact('user', 'preference', 'layoutMatrix', 'ctx', 'customizationMap'));
    }

    public function updatePreset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preset' => 'required|string',
            'density' => 'nullable|in:compact,comfortable,spacious',
        ]);

        $user = $request->user();
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);
        $pref->preset = $validated['preset'];
        if (isset($validated['density'])) {
            $pref->density = $validated['density'];
        }
        $pref->save();

        $this->adaptiveEngine->clearCache($user->id);

        return redirect()->route('workspace.customize')->with('status', 'preset-updated');
    }

    public function saveLayout(Request $request)
    {
        $validated = $request->validate([
            'layout_matrix'                             => 'required|array',
            // Dashboard
            'layout_matrix.dashboard_widgets'           => 'sometimes|array',
            'layout_matrix.dashboard_widgets.top'       => 'nullable|array',
            'layout_matrix.dashboard_widgets.left'      => 'nullable|array',
            'layout_matrix.dashboard_widgets.right'     => 'nullable|array',
            'layout_matrix.quick_actions_order'         => 'nullable|array',
            'layout_matrix.dashboard_kpi_order'         => 'nullable|array',
            // Analytics BI
            'layout_matrix.analytics_widgets'           => 'sometimes|array',
            'layout_matrix.analytics_widgets.top'       => 'nullable|array',
            'layout_matrix.analytics_widgets.left'      => 'nullable|array',
            'layout_matrix.analytics_widgets.right'     => 'nullable|array',
            'layout_matrix.analytics_widgets.bottom'    => 'nullable|array',
            'layout_matrix.analytics_kpi_order'         => 'nullable|array',
            'layout_matrix.analytics_panels_order'      => 'nullable|array',
            // Finance KPI
            'layout_matrix.finance_kpi_order'           => 'nullable|array',
            // Kitchen KPI & Kanban Panels
            'layout_matrix.kitchen_kpi_order'           => 'nullable|array',
            'layout_matrix.kitchen_panels_order'        => 'nullable|array',
            // Delivery KPI & Delivery Items
            'layout_matrix.delivery_kpi_order'          => 'nullable|array',
            'layout_matrix.delivery_items_order'        => 'nullable|array',
            // Menu Sections, Categories & Items
            'layout_matrix.menu_sections_order'         => 'nullable|array',
            'layout_matrix.menu_categories_order'       => 'nullable|array',
            'layout_matrix.menu_items_order'            => 'nullable|array',
            // Orders Sections, Orders KPI & Orders Items
            'layout_matrix.orders_sections_order'       => 'nullable|array',
            'layout_matrix.orders_kpi_order'            => 'nullable|array',
            'layout_matrix.orders_items_order'          => 'nullable|array',
            // Profile Sidebar Order
            'layout_matrix.profile_sidebar_order'       => 'nullable|array',
            // Cart Sections Order
            'layout_matrix.cart_sections_order'         => 'nullable|array',
            // Component Styles — Shape + Size
            'layout_matrix.component_styles'            => 'sometimes|array',
        ]);

        $user = $request->user();
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);

        // Whitelist: Dashboard
        $allowedWidgets      = ['W-KPI', 'W-QUICK', 'W-ORDERS', 'W-STATUS', 'W-ACTIVITY'];
        $allowedActions      = ['catalog', 'cart', 'orders', 'kitchen', 'delivery', 'finance', 'analytics', 'profile'];
        $allowedDashboardKpi = ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'];
        // Whitelist: Analytics
        $allowedAnalytics       = ['A-KPI', 'A-CHARTS', 'A-TOP-MENU', 'A-ACTIVITY', 'A-SYSTEM'];
        $allowedAnalyticsKpi    = ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'];
        $allowedAnalyticsPanels = ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'];
        // Whitelist: Finance KPI
        $allowedFinanceKpi   = ['lunas', 'menunggu', 'belum_bayar', 'ditolak'];

        $incoming = $validated['layout_matrix'];

        // Ambil layout saat ini dari DB agar partial update tidak menghapus key lain
        $existingMatrix = $pref->effective_layout_matrix;

        $cleanMatrix = $existingMatrix;

        // Dashboard
        if (isset($incoming['dashboard_widgets'])) {
            $cleanMatrix['dashboard_widgets'] = [
                'top'   => array_values(array_unique(array_intersect($incoming['dashboard_widgets']['top']   ?? [], $allowedWidgets))),
                'left'  => array_values(array_unique(array_intersect($incoming['dashboard_widgets']['left']  ?? [], $allowedWidgets))),
                'right' => array_values(array_unique(array_intersect($incoming['dashboard_widgets']['right'] ?? [], $allowedWidgets))),
            ];
        }
        if (isset($incoming['quick_actions_order'])) {
            $cleanMatrix['quick_actions_order'] = array_values(array_unique(array_intersect($incoming['quick_actions_order'], $allowedActions)));
        }
        if (isset($incoming['dashboard_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['dashboard_kpi_order'], $allowedDashboardKpi)));
            $cleanMatrix['dashboard_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedDashboardKpi)));
        }

        // Analytics
        if (isset($incoming['analytics_widgets'])) {
            $cleanMatrix['analytics_widgets'] = [
                'top'    => array_values(array_unique(array_intersect($incoming['analytics_widgets']['top']    ?? [], $allowedAnalytics))),
                'left'   => array_values(array_unique(array_intersect($incoming['analytics_widgets']['left']   ?? [], $allowedAnalytics))),
                'right'  => array_values(array_unique(array_intersect($incoming['analytics_widgets']['right']  ?? [], $allowedAnalytics))),
                'bottom' => array_values(array_unique(array_intersect($incoming['analytics_widgets']['bottom'] ?? [], $allowedAnalytics))),
            ];
        }
        if (isset($incoming['analytics_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['analytics_kpi_order'], $allowedAnalyticsKpi)));
            $cleanMatrix['analytics_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedAnalyticsKpi)));
        }
        if (isset($incoming['analytics_panels_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['analytics_panels_order'], $allowedAnalyticsPanels)));
            $cleanMatrix['analytics_panels_order'] = array_values(array_unique(array_merge($filtered, $allowedAnalyticsPanels)));
        }

        // Finance KPI
        if (isset($incoming['finance_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['finance_kpi_order'], $allowedFinanceKpi)));
            $cleanMatrix['finance_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedFinanceKpi)));
        }

        // Kitchen KPI & Kanban Panels
        $allowedKitchenKpi    = ['waiting', 'cooking', 'packing', 'done'];
        $allowedKitchenPanels = ['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'];

        if (isset($incoming['kitchen_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['kitchen_kpi_order'], $allowedKitchenKpi)));
            $cleanMatrix['kitchen_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedKitchenKpi)));
        }

        if (isset($incoming['kitchen_panels_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['kitchen_panels_order'], $allowedKitchenPanels)));
            $cleanMatrix['kitchen_panels_order'] = array_values(array_unique(array_merge($filtered, $allowedKitchenPanels)));
        }

        // Delivery KPI & Delivery Items
        $allowedDeliveryKpi = ['waiting', 'on_delivery', 'done', 'total'];

        if (isset($incoming['delivery_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['delivery_kpi_order'], $allowedDeliveryKpi)));
            $cleanMatrix['delivery_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedDeliveryKpi)));
        }

        if (isset($incoming['delivery_items_order'])) {
            $cleanMatrix['delivery_items_order'] = array_values(array_unique(array_map(
                fn($id) => (int)$id,
                array_filter($incoming['delivery_items_order'], fn($id) => is_numeric($id) && (int)$id > 0)
            )));
        }

        // Menu Sections (Header, Toolbar, Grid)
        $allowedMenuSections = ['M-HEADER', 'M-TOOLBAR', 'M-GRID'];
        if (isset($incoming['menu_sections_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['menu_sections_order'], $allowedMenuSections)));
            $cleanMatrix['menu_sections_order'] = array_values(array_unique(array_merge($filtered, $allowedMenuSections)));
        }

        // Menu Categories (slug strings — tidak di-whitelist karena kategori dinamis dari DB)
        if (isset($incoming['menu_categories_order'])) {
            $cleanMatrix['menu_categories_order'] = array_values(array_map(
                fn($s) => preg_replace('/[^a-z0-9\-_]/', '', (string)$s),
                $incoming['menu_categories_order']
            ));
        }

        // Menu Items Order (numeric menu IDs)
        if (isset($incoming['menu_items_order'])) {
            $cleanMatrix['menu_items_order'] = array_values(array_unique(array_map(
                fn($id) => (int)$id,
                array_filter($incoming['menu_items_order'], fn($id) => is_numeric($id) && (int)$id > 0)
            )));
        }

        // Whitelist: Orders Sections
        $allowedOrdersSections = ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'];

        if (isset($incoming['orders_sections_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['orders_sections_order'], $allowedOrdersSections)));
            $cleanMatrix['orders_sections_order'] = array_values(array_unique(array_merge($filtered, $allowedOrdersSections)));
        }

        // Whitelist: Orders KPI
        $allowedOrdersKpi = ['pending', 'preparing', 'on_delivery', 'completed'];

        if (isset($incoming['orders_kpi_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['orders_kpi_order'], $allowedOrdersKpi)));
            $cleanMatrix['orders_kpi_order'] = array_values(array_unique(array_merge($filtered, $allowedOrdersKpi)));
        }

        if (isset($incoming['orders_items_order'])) {
            $cleanMatrix['orders_items_order'] = array_values(array_unique(array_map(
                fn($id) => (int)$id,
                array_filter($incoming['orders_items_order'], fn($id) => is_numeric($id) && (int)$id > 0)
            )));
        }

        // Whitelist: Profile Sidebar
        $allowedProfileSidebar = ['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'];

        if (isset($incoming['profile_sidebar_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['profile_sidebar_order'], $allowedProfileSidebar)));
            $cleanMatrix['profile_sidebar_order'] = array_values(array_unique(array_merge($filtered, $allowedProfileSidebar)));
        }

        // Whitelist: Cart Sections
        $allowedCartSections = ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'];

        if (isset($incoming['cart_sections_order'])) {
            $filtered = array_values(array_unique(array_intersect($incoming['cart_sections_order'], $allowedCartSections)));
            $cleanMatrix['cart_sections_order'] = array_values(array_unique(array_merge($filtered, $allowedCartSections)));
        }

        // Component Styles — Shape + Size
        if (isset($incoming['component_styles']) && is_array($incoming['component_styles'])) {
            $allowedShapes   = ['rectangle', 'rounded', 'sharp', 'pill', 'circle', 'hexagon'];
            // Komponen yang diizinkan per halaman
            $allowedComponents = [
                'dashboard' => ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04', 'W-QUICK', 'W-ORDERS', 'W-STATUS', 'W-ACTIVITY', 'W-KPI'],
                'analytics' => ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06', 'A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM', 'A-KPI', 'A-CHARTS'],
                'finance'   => ['lunas', 'menunggu', 'belum_bayar', 'ditolak'],
                'kitchen'   => ['waiting', 'cooking', 'packing', 'done', 'k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'],
                'delivery'  => ['waiting', 'on_delivery', 'done', 'total', 'delivery_order_1'],
                'orders'    => ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION', 'pending', 'preparing', 'on_delivery', 'completed', 'orders_item_1'],
                'menus'     => ['M-HEADER', 'M-TOOLBAR', 'M-GRID', 'category_pills', 'menu_item_1'],
                'profile'   => ['P-HEADER', 'P-FORM', 'P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'],
                'cart'      => ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'],
            ];
            // Constraint ukuran per halaman
            $sizeConstraints = [
                'dashboard' => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'analytics' => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'finance'   => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'kitchen'   => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'delivery'  => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'orders'    => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'menus'     => ['min_w' => 140, 'max_w' => 900, 'min_h' => 36,  'max_h' => 600],
                'profile'   => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
                'cart'      => ['min_w' => 140, 'max_w' => 900, 'min_h' => 60,  'max_h' => 600],
            ];

            $cleanStyles = $cleanMatrix['component_styles'] ?? [];

            foreach ($incoming['component_styles'] as $page => $components) {
                if (!isset($allowedComponents[$page]) || !is_array($components)) continue;
                $c       = $sizeConstraints[$page];
                $allowed = $allowedComponents[$page];

                foreach ($components as $compId => $style) {
                    if (!in_array($compId, $allowed) && !str_starts_with((string)$compId, 'orders_item_') && !str_starts_with((string)$compId, 'delivery_order_') && !str_starts_with((string)$compId, 'menu_item_')) continue;
                    if (!is_array($style)) continue;

                    $validShapesForComp = WorkspacePreference::getCompatibleShapes((string)$compId);
                    $shape  = in_array($style['shape'] ?? '', $validShapesForComp, true) ? $style['shape'] : 'rectangle';
                    $width  = (int) max($c['min_w'], min($c['max_w'],  $style['width']  ?? $c['min_w']));
                    $height = (int) max($c['min_h'], min($c['max_h'],  $style['height'] ?? $c['min_h']));
                    $radius = (int) max(0,            min(9999, $style['border_radius'] ?? 16));

                    $cleanStyles[$page][$compId] = [
                        'shape'         => $shape,
                        'width'         => $width,
                        'height'        => $height,
                        'border_radius' => $radius,
                    ];
                }
            }
            $cleanMatrix['component_styles'] = $cleanStyles;
        }

        $pref->layout_matrix = $cleanMatrix;
        $pref->save();

        $this->adaptiveEngine->clearCache($user->id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'        => 'success',
                'message'       => 'Konfigurasi tata letak antarmuka berhasil disimpan secara permanen.',
                'layout_matrix' => $cleanMatrix,
            ]);
        }

        return redirect()->back()->with('status', 'layout-saved');
    }

    public function resetLayout(Request $request)
    {
        // Bisa reset semua halaman (default) atau hanya halaman tertentu via 'page' param
        $page = $request->input('page', 'all');

        $user = $request->user();
        $pref = WorkspacePreference::firstOrCreate(['user_id' => $user->id]);

        $defaultMatrix  = WorkspacePreference::getDefaultLayoutMatrix();
        $existingMatrix = $pref->effective_layout_matrix;

        if ($page === 'all') {
            $resetMatrix = $defaultMatrix;
        } else {
            // Reset hanya key yang relevan dengan halaman yang diminta
            $pageKeyMap = [
                'dashboard' => ['dashboard_widgets', 'quick_actions_order', 'dashboard_kpi_order'],
                'analytics' => ['analytics_widgets', 'analytics_kpi_order', 'analytics_panels_order'],
                'finance'   => ['finance_kpi_order'],
                'menus'     => ['menu_categories_order', 'menu_items_order', 'menu_sections_order'],
                'kitchen'   => ['kitchen_kpi_order', 'kitchen_panels_order'],
                'delivery'  => ['delivery_kpi_order', 'delivery_items_order'],
                'orders'    => ['orders_kpi_order', 'orders_items_order', 'orders_sections_order'],
                'profile'   => ['profile_sidebar_order'],
                'cart'      => ['cart_sections_order'],
            ];
            $keysToReset = $pageKeyMap[$page] ?? [];
            $resetMatrix = $existingMatrix;
            foreach ($keysToReset as $key) {
                $resetMatrix[$key] = $defaultMatrix[$key] ?? [];
            }
            // Juga reset component_styles untuk halaman tersebut
            if (isset($defaultMatrix['component_styles'][$page])) {
                if (!isset($resetMatrix['component_styles'])) {
                    $resetMatrix['component_styles'] = [];
                }
                $resetMatrix['component_styles'][$page] = $defaultMatrix['component_styles'][$page];
            }
        }

        $pref->layout_matrix = $resetMatrix;
        $pref->save();

        $this->adaptiveEngine->clearCache($user->id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'        => 'success',
                'message'       => 'Tampilan antarmuka berhasil dikembalikan ke susunan default.',
                'layout_matrix' => $resetMatrix,
            ]);
        }

        return redirect()->back()->with('status', 'layout-reset');
    }
}

