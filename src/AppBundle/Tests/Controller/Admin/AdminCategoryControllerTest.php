<?php

declare(strict_types=1);

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
        $crawler = $client->request('GET', '/uk/admin/categories');

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
        $crawler = $client->request('GET', "/uk/admin/category_root/new");

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
        $crawler = $client->request('GET', "/uk/admin/category/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testCategoryEdit()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $slug = $em
            ->getRepository(\AppBundle\Entity\Category::class)
            ->findOneBy([])->getSlug();
        $crawler = $client->request('GET', "/uk/admin/category/edit/{$slug}");

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
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $slug = $em
            ->getRepository(\AppBundle\Entity\Category::class)
            ->findOneBy([])->getSlug();

        $crawler = $client->request('GET', "/uk/admin/category/edit/{$slug}");

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testCategoryUpDown()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));

        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $repo = $em->getRepository(\AppBundle\Entity\Category::class);
        $category = $em
            ->getRepository(\AppBundle\Entity\Category::class)
            ->findOneBy(array('parent' => null));
        $children = $repo->children($category);
        $child = $children[0];
        $title1 = $child->getTitle();

        $client->request('GET', "/uk/admin/category/down/{$child->getSlug()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $category = $em
            ->getRepository(\AppBundle\Entity\Category::class)
            ->findOneBy(array('parent' => null));
        $children = $repo->children($category);
        $child = $children[1];
        $title2 = $child->getTitle();

        $this->assertEquals($title1, $title2);
        $client->request('GET', "/uk/admin/category/up/{$child->getSlug()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $repo = $em->getRepository(\AppBundle\Entity\Category::class);
        $category = $em
            ->getRepository(\AppBundle\Entity\Category::class)
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
        $em = $client->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $slug = $em
            ->getRepository(\AppBundle\Entity\Category::class)
            ->findOneBy([])->getSlug();

        $client->request('GET', "/uk/admin/category/up/{$slug}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $client->request('GET', "/uk/admin/category/down/{$slug}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
