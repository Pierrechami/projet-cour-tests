<?php

namespace App\Service\Customer;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

        private function getSession(): Session
    {
        return $this->requestStack->getSession();
    }

        public function getShippingAddressForSession(EntityManagerInterface $em): ?Address
    {
        $customerId = $this->getSession()->get('customer_id');
        if (!$customerId){
            return null;
        } 
        $customer = $em->getRepository(\App\Entity\Customer::class)->find($customerId);
        return $customer ? $customer->getDefaultShippingAddress() : null;
    }
}
