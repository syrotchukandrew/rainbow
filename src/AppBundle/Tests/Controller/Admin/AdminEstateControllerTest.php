<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminEstateControllerTest extends BaseTestController
{
    public function testIndex()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEstates()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin/estates');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testEstateShow()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/estate/show/{$slug}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testNewEstate()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/estate/new");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testEstateEdit()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([])->getSlug();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', "/ru/admin/estate/edit/{$slug}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testDoMainFoto()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $estate = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([]);
        $files = $estate->getFiles();
        $foto = $files[0];

        $this->assertNull($estate->getMainFoto());
        $client->request('GET', "/ru/admin/do_main_foto/{$estate->getSlug()}/{$foto->getId()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals($estate->getMainFoto(), $foto);
    }
}