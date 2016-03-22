<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Estate;
use AppBundle\Entity\Category;

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
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy([])->getTitle();
        $crawler = $client->request('GET', "en/show_category/{$slug}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter('h3')->count()
        );
    }


}
