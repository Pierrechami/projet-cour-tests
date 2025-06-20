<?php

namespace App\Tests\Controller;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomerControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $customerRepository;
    private string $path = '/customer/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->customerRepository = $this->manager->getRepository(Customer::class);

        foreach ($this->customerRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Customer index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'customer[name]' => 'Testing',
            'customer[email]' => 'Testing',
            'customer[defaultShippingAddress]' => 'Testing',
            'customer[defaultBillingAddress]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->customerRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Customer();
        $fixture->setName('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDefaultShippingAddress('My Title');
        $fixture->setDefaultBillingAddress('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Customer');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Customer();
        $fixture->setName('Value');
        $fixture->setEmail('Value');
        $fixture->setDefaultShippingAddress('Value');
        $fixture->setDefaultBillingAddress('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'customer[name]' => 'Something New',
            'customer[email]' => 'Something New',
            'customer[defaultShippingAddress]' => 'Something New',
            'customer[defaultBillingAddress]' => 'Something New',
        ]);

        self::assertResponseRedirects('/customer/');

        $fixture = $this->customerRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getDefaultShippingAddress());
        self::assertSame('Something New', $fixture[0]->getDefaultBillingAddress());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Customer();
        $fixture->setName('Value');
        $fixture->setEmail('Value');
        $fixture->setDefaultShippingAddress('Value');
        $fixture->setDefaultBillingAddress('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/customer/');
        self::assertSame(0, $this->customerRepository->count([]));
    }
}
