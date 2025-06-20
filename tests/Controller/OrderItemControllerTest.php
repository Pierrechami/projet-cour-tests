<?php

namespace App\Tests\Controller;

use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $orderItemRepository;
    private string $path = '/order/item/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->orderItemRepository = $this->manager->getRepository(OrderItem::class);

        foreach ($this->orderItemRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('OrderItem index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'order_item[productId]' => 'Testing',
            'order_item[quantity]' => 'Testing',
            'order_item[unitPrice]' => 'Testing',
            'order_item[totalPrice]' => 'Testing',
            'order_item[order]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->orderItemRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new OrderItem();
        $fixture->setProductId('My Title');
        $fixture->setQuantity('My Title');
        $fixture->setUnitPrice('My Title');
        $fixture->setTotalPrice('My Title');
        $fixture->setOrder('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('OrderItem');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new OrderItem();
        $fixture->setProductId('Value');
        $fixture->setQuantity('Value');
        $fixture->setUnitPrice('Value');
        $fixture->setTotalPrice('Value');
        $fixture->setOrder('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'order_item[productId]' => 'Something New',
            'order_item[quantity]' => 'Something New',
            'order_item[unitPrice]' => 'Something New',
            'order_item[totalPrice]' => 'Something New',
            'order_item[order]' => 'Something New',
        ]);

        self::assertResponseRedirects('/order/item/');

        $fixture = $this->orderItemRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getProductId());
        self::assertSame('Something New', $fixture[0]->getQuantity());
        self::assertSame('Something New', $fixture[0]->getUnitPrice());
        self::assertSame('Something New', $fixture[0]->getTotalPrice());
        self::assertSame('Something New', $fixture[0]->getOrder());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new OrderItem();
        $fixture->setProductId('Value');
        $fixture->setQuantity('Value');
        $fixture->setUnitPrice('Value');
        $fixture->setTotalPrice('Value');
        $fixture->setOrder('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/order/item/');
        self::assertSame(0, $this->orderItemRepository->count([]));
    }
}
