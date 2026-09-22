<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\WorkspacePreference;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = $request->query('category');
        $search           = $request->query('search');

        $query = Menu::with(['category', 'vendor'])->where('is_available', true);

        if ($selectedCategory) {
            $query->whereHas('category', function ($q) use ($selectedCategory) {
                $q->where('slug', $selectedCategory);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menus      = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        // Ambil layout preferensi user untuk menu_categories_order & menu_sections_order
        $user = $request->user();
        if ($user) {
            $preference = WorkspacePreference::firstOrCreate(
                ['user_id' => $user->id],
                ['layout_density' => 'comfortable']
            );
            $layoutMatrix = $preference->effective_layout_matrix;
        } else {
            $layoutMatrix = WorkspacePreference::getDefaultLayoutMatrix();
        }
        $menuSectionsOrder   = $layoutMatrix['menu_sections_order'] ?? ['M-HEADER', 'M-TOOLBAR', 'M-GRID'];
        $menuCategoriesOrder = $layoutMatrix['menu_categories_order'] ?? [];

        // Terapkan urutan kategori tersimpan (jika tidak kosong)
        if (!empty($menuCategoriesOrder)) {
            $ordered   = collect($menuCategoriesOrder);
            $categories = $categories->sortBy(function ($cat) use ($ordered) {
                $pos = $ordered->search($cat->slug);
                return $pos !== false ? $pos : PHP_INT_MAX;
            })->values();
        }

        $menuItemsOrder = $layoutMatrix['menu_items_order'] ?? [];

        // Terapkan urutan item menu tersimpan (jika tidak kosong)
        if (!empty($menuItemsOrder)) {
            $orderedItems = collect($menuItemsOrder)->map(fn($id) => (int)$id);
            $sortedItems  = $menus->getCollection()->sortBy(function ($m) use ($orderedItems) {
                $pos = $orderedItems->search($m->id);
                return $pos !== false ? $pos : PHP_INT_MAX;
            })->values();
            $menus->setCollection($sortedItems);
        }

        $defaults = WorkspacePreference::getDefaultLayoutMatrix();
        $menuComponentStyles = $layoutMatrix['component_styles']['menus']
            ?? $defaults['component_styles']['menus']
            ?? ['category_pills' => ['shape' => 'pill', 'width' => 200, 'height' => 44, 'border_radius' => 9999]];

        return view('menus.index', compact(
            'menus', 'categories', 'selectedCategory', 'search', 'layoutMatrix', 'menuSectionsOrder', 'menuCategoriesOrder', 'menuItemsOrder', 'menuComponentStyles'
        ));
    }
}
