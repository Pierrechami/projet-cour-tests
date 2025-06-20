<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerForm;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/step2', name: 'checkout_step2')]
    public function step2(Request $request, EntityManagerInterface $em): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($customer);
            $customer->getDefaultShippingAddress()->setCustomer($customer);
            $customer->getDefaultBillingAddress()->setCustomer($customer);

            $em->flush();

            $request->getSession()->set('customer_id', $customer->getId());

            return $this->redirectToRoute('checkout_step3');
        }

        return $this->render('checkout/step2.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

