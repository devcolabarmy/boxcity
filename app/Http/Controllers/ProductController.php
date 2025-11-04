<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Small helper to reuse the availability condition everywhere.
     * Available = inStock = 1 OR quantity > 0
     */
    // NEW: availability helper
    private function availableProducts()
    {
        return Product::query()->where(function ($q) {
            $q->where('inStock', 1)
              ->orWhere('quantity', '>', 0);
        });
    }

    /**
     * Display the main product listing page with category hierarchy and product size range statistics.
     */
    public function index(): View
    {
        $rootCategories = Category::with('children')->whereNull('parentId')->get();

        $categories = $rootCategories->map(function ($category) {
            return $this->buildCategoryTreeWithCounts($category);
        });

        // NEW: compute size buckets ONLY from available products
        $rawCounts = $this->availableProducts()->selectRaw("
            COUNT(CASE WHEN CAST(length AS UNSIGNED) BETWEEN 3 AND 8  THEN 1 END) AS range_3_8,
            COUNT(CASE WHEN CAST(length AS UNSIGNED) BETWEEN 9 AND 11 THEN 1 END) AS range_9_11,
            COUNT(CASE WHEN CAST(length AS UNSIGNED) BETWEEN 12 AND 13 THEN 1 END) AS range_12_13,
            COUNT(CASE WHEN CAST(length AS UNSIGNED) BETWEEN 14 AND 17 THEN 1 END) AS range_14_17,
            COUNT(CASE WHEN CAST(length AS UNSIGNED) BETWEEN 18 AND 23 THEN 1 END) AS range_18_23
        ")->first();

        $sizes = [
            ['label' => '3" - 8"',  'count' => $rawCounts->range_3_8,  'min' => 3,  'max' => 8 ],
            ['label' => '9" - 11"', 'count' => $rawCounts->range_9_11, 'min' => 9,  'max' => 11],
            ['label' => '12" - 13"','count' => $rawCounts->range_12_13,'min' => 12, 'max' => 13],
            ['label' => '14" - 17"','count' => $rawCounts->range_14_17,'min' => 14, 'max' => 17],
            ['label' => '18" - 23"','count' => $rawCounts->range_18_23,'min' => 18, 'max' => 23],
        ];

        return view('web.index', ['categories' => $categories, 'sizes' => $sizes ]);
    }

    public function paginateProducts(Request $request)
    {
        // NEW: only paginate available products
        $products = $this->availableProducts()->paginate(60)->withPath(url('/'));

        if ($request->ajax()) {
            // NOTE: if 'partials.products_ajax' doesn't exist, switch to 'partials.product_list'
            return view('partials.products_ajax', compact('products'))->render();
        }

        return view('web.index', compact('products'));
    }

    /**
     * Display the product details page.
     */
    public function detail(int $id): View
    {
        // Keep as-is; optional: you could also gate detail to available()
        $product = Product::where('productID', $id)->firstOrFail();

        return view('web.product.detail', ['product' => $product]);
    }

    /**
     * Load more products and return HTML content for pagination.
     */
    public function loadMoreProducts(): JsonResponse
    {
        try {
            // NEW: only load more for available products
            $products = $this->availableProducts()->paginate(60);

            // If you don't have 'partials.product_list', adjust the view name you actually use
            $productHtml = view('partials.product_list', ['products' => $products])->render();

            return response()->json([
                'product_html'  => $productHtml,
                'next_page_url' => $products->nextPageUrl(),
                'has_more'      => $products->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load more products', ['exception' => $e]);

            return response()->json([
                'error'   => 'Failed to load more products.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function buildCategoryTreeWithCounts($category)
    {
        $categoryIds = collect([$category->categoryId]);
        $this->collectDescendantCategoryIds($category, $categoryIds);

        // NEW: count only available products inside the category subtree
        $totalProductCount = $this->availableProducts()
            ->whereIn('categoryId', $categoryIds)
            ->count();

        return [
            'categoryId'        => $category->categoryId,
            'categoryName'      => $category->categoryName,
            'totalProductCount' => $totalProductCount,
            'children'          => $category->children->map(function ($child) {
                return $this->buildCategoryTreeWithCounts($child);
            })->toArray()
        ];
    }

    public function collectDescendantCategoryIds($category, &$ids)
    {
        foreach ($category->children as $child) {
            $ids->push($child->categoryId);
            $this->collectDescendantCategoryIds($child, $ids);
        }
    }
}
