<?php

namespace App\Tests\Controller;

use App\Service\Product\ProductService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductsControllerTest extends WebTestCase
{
    private $client;
    private $mockProductService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->mockProductService = $this->createMock(ProductService::class);
        $container->set(ProductService::class, $this->mockProductService);
    }

    public function testIndexDisplaysProducts(): void
    {
        $products = [
            [
                'id' => '1',
                'name' => 'Produit Test',
                'description_short' => 'Desc courte',
                'price' => 10,
                'category' => 'cat1',
            ]
        ];
        $this->mockProductService->method('getPaginatedProducts')->willReturn([
            'items' => $products,
            'total' => 1
        ]);

        $crawler = $this->client->request('GET', '/products');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Catalogue des produits');
        self::assertSelectorTextContains('.product-item h3', 'Produit Test');
    }

    public function testIndexWithPaginationAndFilters(): void
    {
        $products = [
            [
                'id' => '2',
                'name' => 'Produit Filtré',
                'description_short' => 'Desc',
                'price' => 20,
                'category' => 'cat2',
            ]
        ];
        $this->mockProductService->method('getPaginatedProducts')->with(2, 12, 'cat2', 'Filtré')->willReturn([
            'items' => $products,
            'total' => 1
        ]);

        $crawler = $this->client->request('GET', '/products?page=2&category=cat2&name=Filtré');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.product-item h3', 'Produit Filtré');
    }

    public function testIndexNoProducts(): void
    {
        $this->mockProductService->method('getPaginatedProducts')->willReturn([
            'items' => [],
            'total' => 0
        ]);
        $crawler = $this->client->request('GET', '/products');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.product-list p', 'Aucun produit trouvé.');
    }

    public function testShowProductFound(): void
    {
        $products = [
            [
                'id' => '1',
                'name' => 'Produit Test',
                'description_short' => 'Desc courte',
                'description_long' => 'Desc longue',
                'price' => 10,
                'currency' => 'EUR',
                'category' => 'cat1',
                'stock' => 5,
                'images' => [],
            ]
        ];
        $this->mockProductService->method('getAllApi')->willReturn($products);
        $crawler = $this->client->request('GET', '/products/1');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Produit Test');
        self::assertStringContainsString('Prix : 10 EUR', $crawler->filter('body')->text());
        self::assertStringContainsString('Description courte : Desc courte', $crawler->filter('body')->text());
    }

    public function testShowProductNotFound(): void
    {
        $this->mockProductService->method('getAllApi')->willReturn([]);
        $crawler = $this->client->request('GET', '/products/999');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Produit introuvable.');
        self::assertSelectorTextContains('p', "Le produit demandé n'existe pas.");
    }
}
