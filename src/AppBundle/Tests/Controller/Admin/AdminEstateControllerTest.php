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
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([])->getSlug();
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
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:Estate')
            ->findOneBy([])->getSlug();
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

        $estateId = $estate->getId();
        $fotoId = $foto->getId();
        $estate->setMainFoto(null);
        $em->flush();
        $this->assertNull($estate->getMainFoto());
        $client->request('GET', "/ru/admin/do_main_foto/{$estate->getSlug()}/{$fotoId}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $em->clear();
        $freshEstate = $em->getRepository('AppBundle:Estate')->find($estateId);
        $this->assertNotNull($freshEstate->getMainFoto());
        $this->assertEquals($fotoId, $freshEstate->getMainFoto()->getId());
    }
}