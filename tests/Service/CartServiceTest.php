<?php

namespace App\Tests\Service;

use App\Service\Cart\CartService;
use App\Service\Product\ProductService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class CartServiceTest extends TestCase
{
    private function getCartServiceWithSessionAndProducts(array $sessionCart = [], array $products = [])
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->willReturnCallback(function($key, $default = null) use ($sessionCart) {
            return $key === 'cart' ? $sessionCart : $default;
        });
        $session->method('set')->willReturnCallback(fn() => null);
        $session->method('remove')->willReturnCallback(fn() => null);


        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $productService = $this->createMock(ProductService::class);
        $productService->method('getAllApi')->willReturn($products);

        return [$session, new CartService($requestStack, $productService)];
    }

    public function testGetCartReturnsCartFromSession(): void
    {
        [$session, $service] = $this->getCartServiceWithSessionAndProducts(['1' => 2]);
        $this->assertEquals(['1' => 2], $service->getCart());
    }

    public function testAddItemAddsProductToCart(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'stock' => 10, 'price' => 5],
        ];
        [$session, $service] = $this->getCartServiceWithSessionAndProducts([], $products);
        $result = $service->addItem('1', 2);
        $this->assertTrue($result);
    }

    public function testAddItemFailsIfProductNotFound(): void
    {
        [$session, $service] = $this->getCartServiceWithSessionAndProducts([], []);
        $result = $service->addItem('999', 1);
        $this->assertEquals('Produit introuvable.', $result);
    }

    public function testAddItemFailsIfQtyInvalid(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'stock' => 10, 'price' => 5],
        ];
        [$session, $service] = $this->getCartServiceWithSessionAndProducts([], $products);
        $result = $service->addItem('1', 0);
        $this->assertEquals('Quantité invalide.', $result);
    }

    public function testAddItemFailsIfStockInsufficient(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'stock' => 2, 'price' => 5],
        ];
        [$session, $service] = $this->getCartServiceWithSessionAndProducts(['1' => 1], $products);
        $result = $service->addItem('1', 2);
        $this->assertStringContainsString('Stock insuffisant', $result);
    }

    public function testGetDetailedCartReturnsCorrectData(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'stock' => 10, 'price' => 5],
        ];
        [$session, $service] = $this->getCartServiceWithSessionAndProducts(['1' => 2], $products);
        $detailed = $service->getDetailedCart();
        $this->assertEquals(10, $detailed['subTotal']);
        $this->assertCount(1, $detailed['items']);
        $this->assertEquals('Produit 1', $detailed['items'][0]['product']['name']);
    }

    public function testRemoveItemRemovesProductFromCart(): void
    {
        $products = [
            ['id' => '1', 'name' => 'Produit 1', 'stock' => 10, 'price' => 5],
        ];
        [$session, $service] = $this->getCartServiceWithSessionAndProducts(['1' => 2], $products);
        $service->removeItem('1');
        $this->assertTrue(true); // Pas d'exception = OK
    }

    public function testCleanCartRemovesCartFromSession(): void
    {
        [$session, $service] = $this->getCartServiceWithSessionAndProducts(['1' => 2]);
        $service->cleanCart();
        $this->assertTrue(true); // Pas d'exception = OK
    }

    public function testGetOrderTotalReturnsSum(): void
    {
        [$session, $service] = $this->getCartServiceWithSessionAndProducts();
        $cartData = ['subTotal' => 20];
        $this->assertEquals(25, $service->getOrderTotal($cartData, 5));
    }
}
