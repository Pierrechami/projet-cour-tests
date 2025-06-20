<?php

namespace App\Controller;

use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    #[Route('/step1', name: 'checkout_step1')]
    public function step1(CartService $cartService): Response
    {
        $cartData = $cartService->getDetailedCart();

        if (empty($cartData['items'])) {
            return $this->redirectToRoute('cart_index');
        }

        return $this->render('checkout/step1.html.twig', [
            'cartItems' => $cartData['items'],
            'subTotal' => $cartData['subTotal'],
        ]);
    }
}

