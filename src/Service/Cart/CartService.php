<?php

namespace App\Service\Cart;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\Product\ProductService;
use Symfony\Component\HttpFoundation\Session\Session;

class CartService
{
    private RequestStack $requestStack;
    private ProductService $productService;

    public function __construct(RequestStack $requestStack, ProductService $productService)
    {
        $this->requestStack = $requestStack;
        $this->productService = $productService;
    }

    private function getSession(): Session
    {
        return $this->requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->getSession()->get('cart', []);
    }

    public function saveCart(array $cart): void
    {
        $this->getSession()->set('cart', $cart);
    }

    public function addItem(string $productId, int $qty): bool|string
    {
        $products = $this->productService->getAllApi();

        $product = null;
        foreach ($products as $item) {
            if ($item['id'] == $productId) {
                $product = $item;
                break;
            }
        }

        if (!$product) return "Produit introuvable.";
        if ($qty < 1) return "Quantité invalide.";

        $stock = $product['stock'] ?? 0;
        $cart = $this->getCart();
        $existingQty = $cart[$productId] ?? 0;

        if ($existingQty + $qty > $stock) {
            return "Stock insuffisant mon loulou plus que $stock unités.";
        }

        $cart[$productId] = $existingQty + $qty;
        $this->saveCart($cart);

        return true;
    }

    public function getDetailedCart(): array
    {
        $cart = $this->getCart();
        $products = $this->productService->getAllApi();

        $cartItems = [];
        $subTotal = 0;

        foreach ($cart as $productId => $quantity) {
            $product = array_filter($products, fn($p) => $p['id'] == $productId);
            $product = reset($product);

            if ($product) {
                $total = $quantity * $product['price'];
                $subTotal += $total;

                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'total' => $total,
                ];
            }
        }

        return [
            'items' => $cartItems,
            'subTotal' => $subTotal
        ];
    }

    public function removeItem(string $productId): void
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function cleanCart ():void
    {
        $this->requestStack->getSession()->remove('cart');
    }

}
