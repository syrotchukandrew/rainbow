<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Entity\Category;
use AppBundle\Entity\Estate;
use AppBundle\Tests\Controller\BaseTestController;

class PublicEstateApiControllerTest extends BaseTestController
{
    public function testEstateListingReturnsPagedResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertIsArray($data['data']);
        if (!empty($data['data'])) {
            $item = $data['data'][0];
            $this->assertArrayHasKey('primaryImageUrl', $item);
            $this->assertArrayHasKey('secondaryImageUrls', $item);
        }
        $this->assertSame(1, $data['page']);
        $this->assertSame(5, $data['perPage']);
    }

    public function testEstateListingWithCategorySlug(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $category = $em->getRepository(Category::class)->findOneBy([]);
        $this->assertNotNull($category);

        $client->request('GET', '/api/public/estates?category=' . $category->getSlug());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
    }

    public function testEstateListingWithUnknownCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates?category=no-such-category-xyz');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testSearchRequiresCategoryParam(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/search');
        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSearchReturnsPagedResponse(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $category = $em->getRepository(Category::class)->findOneBy([]);
        $this->assertNotNull($category);

        $client->request('GET', '/api/public/search?category=' . $category->getSlug());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertIsArray($data['data']);
        if (!empty($data['data'])) {
            $item = $data['data'][0];
            $this->assertArrayHasKey('primaryImageUrl', $item);
            $this->assertArrayHasKey('secondaryImageUrls', $item);
        }
    }

    public function testSearchWithUnknownCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/search?category=no-such-category-xyz');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testEstateDetailReturnsEstate(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $estate = $em->getRepository(Estate::class)->findOneBy([]);
        $this->assertNotNull($estate);

        $client->request('GET', '/api/public/estates/' . $estate->getSlug());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('floor', $data);
        $this->assertArrayHasKey('district', $data);
        $this->assertArrayHasKey('imageUrls', $data);
        $this->assertArrayHasKey('firstLastFloor', $data);
        $this->assertArrayHasKey('category', $data);
    }

    public function testEstateDetailWithUnknownSlugReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates/no-such-estate-xyz');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
