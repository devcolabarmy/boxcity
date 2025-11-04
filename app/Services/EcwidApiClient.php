<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EcwidApiClient
 * Handles API requests to Ecwid
 *
 * @package App\Services
 */
class EcwidApiClient
{
    /**
     * The Ecwid API base URL
     *
     * @var string
     */
    private string $baseUrl;

    /**
     * The access token for authentication
     *
     * @var string
     */
    private string $accessToken;

    /**
     * The HTTP client
     *
     * @var Client
     */
    private Client $client;

    /**
     * EcwidApiClient constructor.
     */
    public function __construct()
    {
        $this->accessToken = config('ecwid.access_token');
        $this->baseUrl = config('ecwid.api_base_url');
        $this->client = new Client();
    }

    /**
     * Make a GET request to a given API endpoint
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $queryParams = []): ResponseInterface
    {
        $url = $this->baseUrl . $endpoint;

        return $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ],
            'query' => $queryParams,
        ]);
    }

    /**
     * Fetch products from the Ecwid API with pagination
     *
     * @param int $limit
     * @param int $offset
     * @param int|null $category
     * @return array
     * @throws GuzzleException
     */
    public function fetchProducts(int $limit, int $offset, int $category = null): array
    {
        $response = $this->get('/products', [
            'category' => $category ?? config('ecwid.category_id'),
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetch Category from the Ecwid API with pagination
     *
     * @param int $limit
     * @param int $offset
     * @param int|null $category
     * @return array
     * @throws GuzzleException
     */
    public function fetchCategories(int $limit, int $offset, int $category = null): array
    {
        $response = $this->get('/categories', [
            'withSubcategories' => 'true',
            'hidden_categories' => 'true',
            'parentIds' => $category ?? config('ecwid.category_id'),
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
    /**
     * Post an order to the Ecwid API
     *
     * @param array $orderData
     * @return array
     * @throws GuzzleException
     */

    public function postOrder(array $orderData): array
    {
        $url = $this->baseUrl . '/orders';

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $orderData,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return [
                'error' => 'Failed to place the order. Status code: ' . $response->getStatusCode(),
                'status_code' => $response->getStatusCode(),
                'details' => $response->getBody()->getContents(),
            ];
        } catch (ClientException $e) {
            $response = $e->getResponse();

            return [
                'error' => 'Client error while placing the order',
                'status_code' => $response ? $response->getStatusCode() : null,
                'details' => $response ? (string) $response->getBody() : null,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'An error occurred while placing the order: ' . $e->getMessage(),
                'exception' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch shipping options from the Ecwid API.
     *
     * @return array
     */
    public function fetchShippingOptions(): array
    {
        $url = $this->baseUrl . '/profile/shippingOptions';

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return [];
        }
    }
}
