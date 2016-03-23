<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Estate;
use AppBundle\Entity\MenuItem;
use AppBundle\Entity\User;

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
            5,
            $crawler->filter('h2'),
            'The homepage displays the right number of estates.'
        );
    }

    public function testShowEstate()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
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
        $categories = $client->getContainer()->get('app.final_category_finder')->findFinalCategories();
        $crawler = $client->request('GET', "en/show_category/{$categories[0]->getTitle()}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            5,
            $crawler->filter('h2')->count()
        );
    }

    public function testShowMenuItem()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', "/en/menu_item");
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $menuItems = $em
            ->getRepository('AppBundle:MenuItem')
            ->findAll();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            count($menuItems),
            $crawler->filter('li')->count()
        );
    }

    public function testShowDescriptionMenuItem()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $menuItems = $em
            ->getRepository('AppBundle:MenuItem')
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
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $estates = $em
            ->getRepository('AppBundle:Estate')
            ->findAll();
        $client->request('GET', "/en/pdf/{$estates[0]->getSlug()}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAddDeleteEstateToFavorites()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([])->getSlug();
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $countBefore = count($user->getEstates());
        $client->request('GET', "/en/add_favorites/{$slug}/{$user->getId()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $countAfter = count($user->getEstates());
        $client->request('GET', "/en/delete_favorites/{$slug}/{$user->getId()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $countComeBack = count($user->getEstates());
        $this->assertEquals($countBefore , ($countAfter - 1), $countComeBack);
    }
}
