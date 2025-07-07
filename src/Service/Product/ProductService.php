<?php

namespace App\Service\Product;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductService
{
    private HttpClientInterface $httpClient;
    private const API_URL = 'http://localhost:3001/products';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getAllApi(): array
    {
        $response = $this->httpClient->request('GET', self::API_URL);
        return json_decode($response->getContent(), true);
    }

    public function getPaginatedProducts(int $page = 1, int $limit = 12, ?string $category = null, ?string $name = null): array
    {
        $allProducts = $this->getAllApi();

        if ($category) {
            $allProducts = array_filter($allProducts, fn($p) => ($p['category'] ?? '') === $category);
        }

        if ($name) {
            $allProducts = array_filter($allProducts, fn($p) => str_contains(strtolower($p['name']), strtolower($name)));
        }

        $total = count($allProducts);
        $offset = ($page - 1) * $limit;
        $items = array_slice($allProducts, $offset, $limit);

        return [
            'items' => array_values($items),
            'total' => $total,
        ];
    }
}
