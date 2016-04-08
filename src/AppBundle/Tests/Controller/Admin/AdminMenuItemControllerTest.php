<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminMenuItemControllerTest extends BaseTestController
{
    public function testShowItems()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin/menu_items');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testShowItem()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $id = $em
            ->getRepository('AppBundle:MenuItem')
            ->findOneBy([])->getId();
        $crawler = $client->request('GET', "/ru/admin/menu_item/show/{$id}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h3')
        );
    }

    public function testAddItem()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/menu_item/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testEditItem()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $id = $em
            ->getRepository('AppBundle:MenuItem')
            ->findOneBy([])->getId();
        $crawler = $client->request('GET', "/ru/admin/menu_item/edit/{$id}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testEditItemManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager2',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $id = $em
            ->getRepository('AppBundle:MenuItem')
            ->findOneBy([])->getId();
        $crawler = $client->request('GET', "/ru/admin/menu_item/edit/{$id}");

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}