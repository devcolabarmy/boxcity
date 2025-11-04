<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the product listing page.
     *
     * @return View
     */

    // Only products considered “available”: inStock = 1 OR quantity > 0
private function availableProducts()
{
    return Product::query()->where(function ($q) {
        $q->where('inStock', 1)
          ->orWhere('quantity', '>', 0);
    });
}

    public function index(): View
    {
        $categories = Category::with(['childrenRecursive'])
            ->withCount('products')
            ->whereNull('parentId')
            ->get();

        foreach ($categories as $category) {
            $category->assignTotalProductCount($category);

        }

        dd($categories[0]->childrenRecursive);
        return view('index', compact('categories'));

    }


    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
   public function getProductsByCategoryLevel(Request $request)
{
    $categoryId = $request->get('categoryId');
    $min = $request->get('min');
    $max = $request->get('max');
    $category = $categoryId ? Category::where('categoryId', $categoryId)->first() : null;

    // No filters at all
    if (!$min && !$max && !$category) {
        return $this->bindResponse(
            $this->availableProducts()->paginate(60)
        );
    }

    // Length-only filter
    if ($min && $max && !$category) {
        return $this->bindResponse(
            $this->availableProducts()
                ->lengthBetween($min, $max)
                ->paginate(60)
        );
    }

    // If both length & category given, precompute IDs (optional)
    if ($min && $max && $category) {
        $productsLength = $this->availableProducts()
            ->select('id')
            ->lengthBetween($min, $max)
            ->pluck('id');
    }

    // Category selected — parent with descendants
    if ($category && $category->children()->exists()) {
        $allCategoryIds = collect([$category->categoryId]);
        $this->collectDescendantCategoryIds($category, $allCategoryIds);

        $products = $this->availableProducts()->whereIn('categoryId', $allCategoryIds);

        if (isset($productsLength)) {
            $products->whereIn('id', $productsLength);
        }

        return $this->bindResponse($products->paginate(60));
    }

    // Category selected — leaf
    if ($category) {
        $products = $this->availableProducts()->where('categoryId', $category->categoryId);

        if (isset($productsLength)) {
            $products->whereIn('id', $productsLength);
        }

        return $this->bindResponse($products->paginate(60));
    }

    // Fallback (shouldn’t happen, but keeps response consistent)
    return $this->bindResponse(
        $this->availableProducts()->paginate(60)
    );
}


    // Recursive helper to collect all descendant categoryIds
    public function collectDescendantCategoryIds($category, &$ids)
    {
        foreach ($category->children as $child) {
            $ids->push($child->categoryId);
            $this->collectDescendantCategoryIds($child, $ids);
        }
    }

    protected function bindResponse($products){
        $productHtml = view('partials.product_list', ['products' => $products, 'scroll' => 'false'])->render();

        return response()->json([
            'product_html' => $productHtml
        ]);
    }


}
