<?php

namespace App\Controller;

use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('', name: 'cart_index')]
    public function index(CartService $cartService): Response
    {
        $cartData = $cartService->getDetailedCart();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartData['items'],
            'subTotal' => $cartData['subTotal'],
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(string $id, Request $request, CartService $cartService): RedirectResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $result = $cartService->addItem($id, $quantity);

        if ($result !== true) {
            $this->addFlash('danger', $result);
        } else {
            $this->addFlash('success', "Produit ajouté au panier.");
        }

        return $this->redirectToRoute('show_product', ['id' => $id]);
    }

}
