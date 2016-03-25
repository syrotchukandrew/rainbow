<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminDistrictControllerTest extends BaseTestController
{
    public function testDistricts()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin/districts');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testDistrictShow()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:District')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/district/show/{$slug}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testNewDistrict()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/district/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testDistrictEdit()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:District')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/district/edit/{$slug}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testDistrictEditManager()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:District')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager2',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/district/edit/{$slug}");

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }
}