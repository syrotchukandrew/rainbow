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
        $crawler = $client->request('GET', '/uk/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEstates()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/uk/admin/estates');

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
            ->getRepository(\AppBundle\Entity\Estate::class)
            ->findOneBy([])->getSlug();
        $crawler = $client->request('GET', "/uk/admin/estate/show/{$slug}");

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
        $crawler = $client->request('GET', "/uk/admin/estate/new");

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
            ->getRepository(\AppBundle\Entity\Estate::class)
            ->findOneBy([])->getSlug();
        $crawler = $client->request('GET', "/uk/admin/estate/edit/{$slug}");

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
            ->getRepository(\AppBundle\Entity\Estate::class)
            ->findOneBy([]);
        $files = $estate->getFiles();
        $foto = $files[0];

        $estateId = $estate->getId();
        $fotoId = $foto->getId();
        $estate->setMainFoto(null);
        $em->flush();
        $this->assertNull($estate->getMainFoto());
        $client->request('GET', "/uk/admin/do_main_foto/{$estate->getSlug()}/{$fotoId}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $em->clear();
        $freshEstate = $em->getRepository(\AppBundle\Entity\Estate::class)->find($estateId);
        $this->assertNotNull($freshEstate->getMainFoto());
        $this->assertEquals($fotoId, $freshEstate->getMainFoto()->getId());
    }
}