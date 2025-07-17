<?php

namespace App\Tests\Service\Customer;

use App\Entity\Address;
use App\Entity\Customer;
use App\Service\Customer\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class CustomerServiceTest extends TestCase
{
    public function testReturnsNullIfNoCustomerIdInSession(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->with('customer_id')->willReturn(null);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $service = new CustomerService($requestStack);
        $this->assertNull($service->getShippingAddressForSession($em));
    }

    public function testReturnsNullIfCustomerNotFound(): void
    {
        $customerId = 42;

        $session = $this->createMock(Session::class);
        $session->method('get')->with('customer_id')->willReturn($customerId);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with($customerId)->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Customer::class)->willReturn($repository);

        $service = new CustomerService($requestStack);
        $this->assertNull($service->getShippingAddressForSession($em));
    }

    public function testReturnsNullIfCustomerHasNoShippingAddress(): void
    {
        $customerId = 42;

        $customer = $this->createMock(Customer::class);
        $customer->method('getDefaultShippingAddress')->willReturn(null);

        $session = $this->createMock(Session::class);
        $session->method('get')->with('customer_id')->willReturn($customerId);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with($customerId)->willReturn($customer);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Customer::class)->willReturn($repository);

        $service = new CustomerService($requestStack);
        $this->assertNull($service->getShippingAddressForSession($em));
    }

    public function testReturnsShippingAddressIfCustomerExists(): void
    {
        $customerId = 42;
        $address = $this->createMock(Address::class);

        $customer = $this->createMock(Customer::class);
        $customer->method('getDefaultShippingAddress')->willReturn($address);

        $session = $this->createMock(Session::class);
        $session->method('get')->with('customer_id')->willReturn($customerId);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with($customerId)->willReturn($customer);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Customer::class)->willReturn($repository);

        $service = new CustomerService($requestStack);
        $result = $service->getShippingAddressForSession($em);

        $this->assertSame($address, $result);
    }
}
