<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductsControllerTest extends WebTestCase
{
    public function testProductListingFirstPage(): void
    {
        //arr
        $client = static::createClient();
        //act
        $crawler = $client->request('GET', '/products?page=1');

        //assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.products');
        $this->assertSelectorTextContains('h1', 'Produits - Page 1');
    }

    public function testProductListingSecondPage(): void
    {
        //arr
        $client = static::createClient();
        //act
        $crawler = $client->request('GET', '/products?page=2');

        //assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.products');
        $this->assertSelectorTextContains('h1', 'Produits - Page 2');
    }

    public function testPaginationLinksAreVisible(): void
    {
        //arr
        $client = static::createClient();
        //act
        $crawler = $client->request('GET', '/products?page=1');

        //assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a:contains("Suivant →")');
    }
}
