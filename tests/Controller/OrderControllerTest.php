<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $orderRepository;
    private string $path = '/order/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->orderRepository = $this->manager->getRepository(Order::class);

        foreach ($this->orderRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Order index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'order[orderDate]' => 'Testing',
            'order[carrierId]' => 'Testing',
            'order[paymentId]' => 'Testing',
            'order[orderTotal]' => 'Testing',
            'order[customer]' => 'Testing',
            'order[shippingAddress]' => 'Testing',
            'order[billingAddress]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->orderRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Order();
        $fixture->setOrderDate('My Title');
        $fixture->setCarrierId('My Title');
        $fixture->setPaymentId('My Title');
        $fixture->setOrderTotal('My Title');
        $fixture->setCustomer('My Title');
        $fixture->setShippingAddress('My Title');
        $fixture->setBillingAddress('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Order');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Order();
        $fixture->setOrderDate('Value');
        $fixture->setCarrierId('Value');
        $fixture->setPaymentId('Value');
        $fixture->setOrderTotal('Value');
        $fixture->setCustomer('Value');
        $fixture->setShippingAddress('Value');
        $fixture->setBillingAddress('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'order[orderDate]' => 'Something New',
            'order[carrierId]' => 'Something New',
            'order[paymentId]' => 'Something New',
            'order[orderTotal]' => 'Something New',
            'order[customer]' => 'Something New',
            'order[shippingAddress]' => 'Something New',
            'order[billingAddress]' => 'Something New',
        ]);

        self::assertResponseRedirects('/order/');

        $fixture = $this->orderRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getOrderDate());
        self::assertSame('Something New', $fixture[0]->getCarrierId());
        self::assertSame('Something New', $fixture[0]->getPaymentId());
        self::assertSame('Something New', $fixture[0]->getOrderTotal());
        self::assertSame('Something New', $fixture[0]->getCustomer());
        self::assertSame('Something New', $fixture[0]->getShippingAddress());
        self::assertSame('Something New', $fixture[0]->getBillingAddress());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Order();
        $fixture->setOrderDate('Value');
        $fixture->setCarrierId('Value');
        $fixture->setPaymentId('Value');
        $fixture->setOrderTotal('Value');
        $fixture->setCustomer('Value');
        $fixture->setShippingAddress('Value');
        $fixture->setBillingAddress('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/order/');
        self::assertSame(0, $this->orderRepository->count([]));
    }
}
