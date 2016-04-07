<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminCategoryControllerTest extends BaseTestController
{
    public function testCategories()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin/categories');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testNewCategoryRoot()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/category_root/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testNewCategory()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/category/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testCategoryEdit()
    {
        $this->client = static::createClient();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/category/edit/{$slug}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testCategoryEditManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager2',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy([])->getSlug();

        $crawler = $client->request('GET', "/ru/admin/category/edit/{$slug}");

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testCategoryUpDown()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('AppBundle\Entity\Category');
        $category = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy(array('parent' => null));
        $children = $repo->children($category);
        $child = $children[0];
        $title1 = $child->getTitle();

        $client->request('GET', "/ru/admin/category/down/{$child->getSlug()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $category = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy(array('parent' => null));
        $children = $repo->children($category);
        $child = $children[1];
        $title2 = $child->getTitle();

        $this->assertEquals($title1, $title2);
        $client->request('GET', "/ru/admin/category/up/{$child->getSlug()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $repo = $em->getRepository('AppBundle\Entity\Category');
        $category = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy(array('parent' => null));
        $children = $repo->children($category);
        $child = $children[0];
        $title3 = $child->getTitle();

        $this->assertEquals($title1, $title3);
    }

    public function testCategoryUpDownManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager2',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Category')
            ->findOneBy([])->getSlug();

        $client->request('GET', "/ru/admin/category/up/{$slug}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request('GET', "/ru/admin/category/down/{$slug}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}