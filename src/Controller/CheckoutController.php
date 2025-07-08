<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerForm;
use App\Form\TransporterSelectionFormType;
use App\Service\Cart\CartService;
use App\Service\Carrier\CarrierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Customer\CustomerService; 

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    #[Route('/cart-review', name: 'checkout_cart_review')]
    public function cartReview(CartService $cartService): Response
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

    #[Route('/customer-info', name: 'checkout_customer_info')]
    public function customerInfo(Request $request, EntityManagerInterface $em): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerForm::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipping = $customer->getDefaultShippingAddress();
            $billing = $customer->getDefaultBillingAddress();

            $shipping->setCustomer($customer);
            $billing->setCustomer($customer);

            $em->persist($customer);
            $em->persist($shipping);
            $em->persist($billing);

            $em->flush();

            $request->getSession()->set('customer_id', $customer->getId());

            return $this->redirectToRoute('checkout_carrier_selection');
        }

        return $this->render('checkout/step2.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/carrier-selection', name: 'checkout_carrier_selection')]
    public function carrierSelection(
        Request $request,
        CarrierService $carrierService,
        CartService $cartService
    ): Response {
        $carriers = $carrierService->getCompatibleCarriers();

        $form = $this->createForm(TransporterSelectionFormType::class, null, [
            'carrier_choices' => $carriers,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carrierId = $form->get('carrierId')->getData();
            $request->getSession()->set('selected_carrier_id', $carrierId);

            return $this->redirectToRoute('checkout_payment_confirmation');
        }

        $cartData = $cartService->getDetailedCart();
        return $this->render('checkout/step3.html.twig', [
            'form' => $form->createView(),
            'subTotal' => $cartData['subTotal'],
            'carriers' => $carriers,
        ]);
    }

    #[Route('/payment-confirmation', name: 'checkout_payment_confirmation')]
    public function paymentConfirmation(
        Request $request,
        CartService $cartService,
        CarrierService $carrierService,
        EntityManagerInterface $em,
        CustomerService $customerService
    ): Response {
        $session = $request->getSession();
        $cartData = $cartService->getDetailedCart();

        $shippingAddress = $customerService->getShippingAddressForSession($em);
        $selectedCarrier = $carrierService->getSelectedCarrier($session);
        $shippingCost = $selectedCarrier['price'] ?? 0;
        $total = $cartService->getOrderTotal($cartData, $shippingCost);

        return $this->render('checkout/step4.html.twig', [
            'cartItems' => $cartData['items'],
            'subTotal' => $cartData['subTotal'],
            'shippingAddress' => $shippingAddress,
            'selectedCarrier' => $selectedCarrier,
            'shippingCost' => $shippingCost,
            'total' => $total,
        ]);
    }
}

