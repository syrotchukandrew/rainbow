<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *     $ cd your-symfony-project/
 *     $ phpunit -c app
 */
class SiteControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('#react-estate-listing'),
            'The homepage displays the React estate listing island.'
        );
        $this->assertSame('/api/public/estates', $crawler->filter('#react-estate-listing')->attr('data-api-url'));
    }

    public function testShowEstate()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $slug = $em
            ->getRepository(\App\Entity\Estate::class)
            ->findOneBy([])->getSlug();
        $crawler = $client->request('GET', "/en/show_estate/{$slug}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter('h1')->count()
        );
    }

    public function testShowCategory()
    {
        $client = static::createClient();
        $categories = $client->getContainer()->get(\App\Utils\FinalCategoryFinder::class)->findFinalCategories();
        $crawler = $client->request('GET', "/en/show_category/{$categories[0]->getTitle()}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('#react-estate-listing'),
            'The category page displays the React estate listing island.'
        );
        $dataApiUrl = $crawler->filter('#react-estate-listing')->attr('data-api-url');
        $this->assertStringStartsWith('/api/public/estates?category=', $dataApiUrl);
    }

    public function testShowMenuItem()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "/en/menu_item");
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $menuItems = $em
            ->getRepository(\App\Entity\MenuItem::class)
            ->findAll();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            count($menuItems),
            $crawler->filter('a')->count()
        );
    }

    public function testShowDescriptionMenuItem()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $menuItems = $em
            ->getRepository(\App\Entity\MenuItem::class)
            ->findAll();

        $crawler = $client->request('GET', "/en/description_menu/{$menuItems[0]->getId()}}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testPdfEstate()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $estates = $em
            ->getRepository(\App\Entity\Estate::class)
            ->findAll();
        $client->request('GET', "/en/pdf/{$estates[0]->getSlug()}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAddDeleteEstateToFavorites()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user_manager0',
            'PHP_AUTH_PW' => 'qweasz',
        ]);
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $slug = $em
            ->getRepository(\App\Entity\Estate::class)
            ->findOneBy([])->getSlug();
        $user = $em
            ->getRepository(\App\Entity\User::class)
            ->findOneBy(['username' => 'user_manager0']);
        $userId = $user->getId();
        $countBefore = count($user->getEstates());

        $client->request('GET', "/en/add_favorites/{$slug}/{$userId}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $em->clear();
        $userAfter = $em->getRepository(\App\Entity\User::class)->find($userId);
        $countAfter = count($userAfter->getEstates());

        $client->request('GET', "/en/delete_favorites/{$slug}/{$userId}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $em->clear();
        $userComeBack = $em->getRepository(\App\Entity\User::class)->find($userId);
        $countComeBack = count($userComeBack->getEstates());

        $this->assertEquals($countBefore, ($countAfter - 1), (string) $countComeBack);
    }
}
