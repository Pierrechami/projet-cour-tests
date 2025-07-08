<?php
namespace App\Service\Carrier;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\Cart\CartService;

class CarrierService
{
    private HttpClientInterface $httpClient;
    private CartService $cartService;

    public function __construct(HttpClientInterface $httpClient, CartService $cartService)
    {
        $this->httpClient = $httpClient;
        $this->cartService = $cartService;
    }

    public function fetchAllCarriers(): array
    {
        return $this->httpClient->request('GET', 'http://localhost:3001/carriers')->toArray();
    }

    public function getCompatibleCarriers(): array
    {
        $carriers = $this->fetchAllCarriers();
        $cart = $this->cartService->getDetailedCart();

        $totalWeightGrams = array_reduce($cart['items'], function ($carry, $item) {
            return $carry + (($item['product']['weight'] ?? 0) * $item['quantity']);
        }, 0);

        $totalWeightKg = $totalWeightGrams / 1000;

        return array_filter($carriers, function ($carrier) use ($totalWeightKg) {
            return isset($carrier['max-weight']) && $carrier['max-weight'] >= $totalWeightKg;
        });
    }
    public function getSelectedCarrier($session): ?array
    {
        $selectedCarrierId = $session->get('selected_carrier_id');
        $carriers = $this->getCompatibleCarriers();
        foreach ($carriers as $carrier) {
            if ($carrier['id'] === $selectedCarrierId) {
                return $carrier;
            }
        }
        return null;
    }

}

