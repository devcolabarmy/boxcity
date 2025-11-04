<?php

namespace App\Jobs;


use App\Models\Category;
use App\Models\Product;
use App\Services\EcwidApiClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Class FetchProductsFromApi
 * Fetches products from the Ecwid API and stores them in the database
 *
 * @package App\Jobs
 */
class FetchProductsFromApi
{
    use Dispatchable;
    /**
     * The Ecwid API client instance
     *
     * @var EcwidApiClient
     */
    private EcwidApiClient $ecwidApiClient;

    /**
     * Execute the job to fetch and store the products
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $ecwidApiClient = new EcwidApiClient();
        $limit = config('ecwid.limit');
        $categories = Category::pluck('categoryId');
        foreach ($categories as $category) {
            $offset = 0;
            $totalFetched = 0;

            try {
                $data = $ecwidApiClient->fetchProducts($limit, $offset, $category);
                
                $total = $data['total'];
                $lastCount = 0;

                do {
                    $data = $ecwidApiClient->fetchProducts($limit, $offset, $category);
                    $products = $data['items'];

                    $filteredProducts = $this->filterValidProducts($products);

                    $this->storeProducts($filteredProducts, $category);

                    $totalFetched += count($products);
                    $offset += $limit;

                    if ($lastCount == $totalFetched) {
                        break;
                    }

                    $lastCount = $totalFetched;
                } while ($totalFetched < $total);

            } catch (\Exception $e) {
                Log::error('Failed to fetch products from API', ['error' => $e->getMessage()]);
            }
        }

    }

    /**
     * Filter products to only include those with a positive quantity
     *
     * @param array $products
     * @return array
     */
    private function filterValidProducts(array $products): array
    {
        return array_filter($products, function ($product) {
            return isset($product['quantity']) && $product['quantity'] > 0;
        });
    }

    /**
     * Store or update products in the database.
     *
     * @param array $products
     * @param int|string $category
     * @return void
     */
    public function storeProducts(array $products, $category): void
    {
        foreach ($products as $product) {
            $categoryDetails = $this->resolveDefaultCategory($product, $category);
            $dimensions = $this->extractDimensionsFromProduct($product);

            if (!empty($categoryDetails['id']) && $categoryDetails['name'] !== null) {
                Category::updateOrCreate(
                    ['categoryId' => $categoryDetails['id']],
                    ['categoryName' => $categoryDetails['name']]
                );
            }

            $categoryIdForProduct = $categoryDetails['id'] ?? $category;

            Product::updateOrCreate(
                ['productId' => $product['id']],
                [
                    'name' => $product['name'] ?? '',
                    'price' => $product['price'] ?? '',
                    'thumbnailUrl' => $product['thumbnailUrl'] ?? null,
                    'length' => $dimensions['length'],
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'sku' => $product['sku'] ?? '',
                    'categoryId' => $categoryIdForProduct,
                    'description' => $product['description'] ?? '',
                    'inStock'      => (bool)($product['inStock'] ?? false),
                    'quantity'     => (int)($product['quantity'] ?? 0),
                ]
            );
        }
    }

    /**
     * Determine the default category details for a product.
     *
     * @param array $product
     * @param int|string $fallbackCategoryId
     * @return array{id:int|string|null,name:?string}
     */
    private function resolveDefaultCategory(array $product, $fallbackCategoryId): array
    {
        $categories = $product['categories'] ?? [];

        foreach ($categories as $category) {
            if (($category['enabled'] ?? false) && isset($category['id'])) {
                return [
                    'id' => $category['id'],
                    'name' => $category['name'] ?? null,
                ];
            }
        }

        $defaultCategoryId = $product['defaultCategoryId'] ?? $fallbackCategoryId;

        foreach ($categories as $category) {
            if (($category['id'] ?? null) === $defaultCategoryId) {
                return [
                    'id' => $defaultCategoryId,
                    'name' => $category['name'] ?? null,
                ];
            }
        }

        return [
            'id' => $defaultCategoryId,
            'name' => null,
        ];
    }

    /**
     * Extract normalized length, width and height values from an Ecwid product payload.
     *
     * @param array $product
     * @return array{length: string, width: string, height: string}
     */
    private function extractDimensionsFromProduct(array $product): array
    {
        $attributes = $product['attributes'] ?? [];

        $length = $this->findAttributeValue($attributes, ['length']);
        $width = $this->findAttributeValue($attributes, ['width']);
        $height = $this->findAttributeValue($attributes, ['height', 'depth']);

        $dimensions = [
            'length' => $length ?? ($product['length'] ?? ($product['dimensions']['length'] ?? '')),
            'width' => $width ?? ($product['width'] ?? ($product['dimensions']['width'] ?? '')),
            'height' => $height ?? ($product['height'] ?? ($product['dimensions']['height'] ?? '')),
        ];

        foreach ($dimensions as $key => $value) {
            $dimensions[$key] = is_string($value) ? trim($value) : (string) ($value ?? '');
        }

        return $dimensions;
    }

    /**
     * Find an attribute value (case-insensitive) by possible names.
     *
     * @param array $attributes
     * @param array<int, string> $possibleNames
     * @return string|null
     */
    private function findAttributeValue(array $attributes, array $possibleNames): ?string
    {
        if (empty($attributes)) {
            return null;
        }

        $normalizedNames = array_map(static function ($name) {
            return strtolower(trim($name));
        }, $possibleNames);

        foreach ($attributes as $attribute) {
            if (!isset($attribute['name'], $attribute['value'])) {
                continue;
            }

            $attributeName = strtolower(trim((string) $attribute['name']));

            if (in_array($attributeName, $normalizedNames, true)) {
                $value = $attribute['value'];

                return is_string($value) ? trim($value) : (string) $value;
            }
        }

        return null;
    }
}
