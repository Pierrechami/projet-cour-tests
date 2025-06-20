<?php

namespace App\Tests\Controller;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AddressControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $addressRepository;
    private string $path = '/address/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->addressRepository = $this->manager->getRepository(Address::class);

        foreach ($this->addressRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Address index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'address[street]' => 'Testing',
            'address[city]' => 'Testing',
            'address[zipCode]' => 'Testing',
            'address[country]' => 'Testing',
            'address[customer]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->addressRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Address();
        $fixture->setStreet('My Title');
        $fixture->setCity('My Title');
        $fixture->setZipCode('My Title');
        $fixture->setCountry('My Title');
        $fixture->setCustomer('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Address');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Address();
        $fixture->setStreet('Value');
        $fixture->setCity('Value');
        $fixture->setZipCode('Value');
        $fixture->setCountry('Value');
        $fixture->setCustomer('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'address[street]' => 'Something New',
            'address[city]' => 'Something New',
            'address[zipCode]' => 'Something New',
            'address[country]' => 'Something New',
            'address[customer]' => 'Something New',
        ]);

        self::assertResponseRedirects('/address/');

        $fixture = $this->addressRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getStreet());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getZipCode());
        self::assertSame('Something New', $fixture[0]->getCountry());
        self::assertSame('Something New', $fixture[0]->getCustomer());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Address();
        $fixture->setStreet('Value');
        $fixture->setCity('Value');
        $fixture->setZipCode('Value');
        $fixture->setCountry('Value');
        $fixture->setCustomer('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/address/');
        self::assertSame(0, $this->addressRepository->count([]));
    }
}
