<?php

namespace App\Tests\Service;

use App\Service\Product\ProductService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ProductServiceTest extends TestCase
{
    public function testGetAllApiReturnsArrayOfProducts(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'category' => 'cat1'],
            ['id' => '2', 'name' => 'Produit 2', 'category' => 'cat2'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode($products));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->with('GET', 'http://localhost:3001/products')->willReturn($response);

        $service = new ProductService($httpClient);
        $result = $service->getAllApi();
        $this->assertEquals($products, $result);
    }

    public function testGetPaginatedProductsReturnsCorrectPagination(): void
    {
        $products = [];
        for ($i = 1; $i <= 30; $i++) {
            $products[] = ['id' => (string)$i, 'name' => 'Produit '.$i, 'category' => 'cat'.($i%2+1)];
        }

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode($products));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $service = new ProductService($httpClient);
        $result = $service->getPaginatedProducts(2, 10);
        $this->assertCount(10, $result['items']);
        $this->assertEquals(30, $result['total']);
        $this->assertEquals('11', $result['items'][0]['id']);
    }

    public function testGetPaginatedProductsFiltersByCategory(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'category' => 'cat1'],
            ['id' => '2', 'name' => 'Produit 2', 'category' => 'cat2'],
            ['id' => '3', 'name' => 'Produit 3', 'category' => 'cat1'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode($products));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $service = new ProductService($httpClient);
        $result = $service->getPaginatedProducts(1, 10, 'cat1');
        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals('1', $result['items'][0]['id']);
        $this->assertEquals('3', $result['items'][1]['id']);
    }

    public function testGetPaginatedProductsFiltersByName(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit Alpha', 'category' => 'cat1'],
            ['id' => '2', 'name' => 'Produit Beta', 'category' => 'cat2'],
            ['id' => '3', 'name' => 'Produit Gamma', 'category' => 'cat1'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn(json_encode($products));

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $service = new ProductService($httpClient);
        $result = $service->getPaginatedProducts(1, 10, null, 'beta');
        $this->assertCount(1, $result['items']);
        $this->assertEquals('2', $result['items'][0]['id']);
    }
}
