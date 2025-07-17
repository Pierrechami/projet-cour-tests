<?php

namespace App\Tests\Service;

use App\Service\Carrier\CarrierService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use App\Service\Cart\CartService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CarrierServiceTest extends TestCase
{
    public function testFetchAllCarriersReturnsArray(): void
    {
        $carriers = [
            ['id' => 'trk001', 'max-weight' => 10],
            ['id' => 'trk002', 'max-weight' => 5],
        ];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($carriers);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->with('GET', 'http://localhost:3001/carriers')->willReturn($response);

        $cartService = $this->createMock(CartService::class);
        $service = new CarrierService($httpClient, $cartService);
        $result = $service->fetchAllCarriers();
        $this->assertEquals($carriers, $result);
    }

    public function testGetCompatibleCarriersFiltersByWeight(): void
    {
        $carriers = [
            ['id' => 'trk001', 'max-weight' => 1],
            ['id' => 'trk002', 'max-weight' => 5],
            ['id' => 'trk003', 'max-weight' => 0.5],
        ];
        $cart = [
            'items' => [
                ['product' => ['weight' => 2000], 'quantity' => 2], // 4kg
            ]
        ];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($carriers);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $cartService = $this->createMock(CartService::class);
        $cartService->method('getDetailedCart')->willReturn($cart);

        $service = new CarrierService($httpClient, $cartService);
        $result = $service->getCompatibleCarriers();
        $this->assertCount(1, $result);
        $this->assertEquals('trk002', array_values($result)[0]['id']);
    }

    public function testGetSelectedCarrierReturnsCorrectCarrier(): void
    {
        $carriers = [
            ['id' => 'trk001', 'max-weight' => 10],
            ['id' => 'trk002', 'max-weight' => 5],
        ];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($carriers);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $cartService = $this->createMock(CartService::class);
        $cartService->method('getDetailedCart')->willReturn(['items' => []]);

        $service = new CarrierService($httpClient, $cartService);

        $service = $this->getMockBuilder(CarrierService::class)
            ->setConstructorArgs([$httpClient, $cartService])
            ->onlyMethods(['getCompatibleCarriers'])
            ->getMock();
        $service->method('getCompatibleCarriers')->willReturn($carriers);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('selected_carrier_id')->willReturn('trk002');

        $selected = $service->getSelectedCarrier($session);
        $this->assertEquals('trk002', $selected['id']);
    }

    public function testGetSelectedCarrierReturnsNullIfNotFound(): void
    {
        $carriers = [
            ['id' => 'trk001', 'max-weight' => 10],
        ];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($carriers);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $cartService = $this->createMock(CartService::class);
        $cartService->method('getDetailedCart')->willReturn(['items' => []]);

        $service = $this->getMockBuilder(CarrierService::class)
            ->setConstructorArgs([$httpClient, $cartService])
            ->onlyMethods(['getCompatibleCarriers'])
            ->getMock();
        $service->method('getCompatibleCarriers')->willReturn($carriers);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('selected_carrier_id')->willReturn('trk999');

        $selected = $service->getSelectedCarrier($session);
        $this->assertNull($selected);
    }
}
