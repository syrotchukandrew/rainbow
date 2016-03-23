<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class AdminDistrictControllerTest extends BaseTestController
{
    public function testAdministratorUsersCanAccessToTheBackend()
    {
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/ru/admin/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    /*public function testDistricts()
    {
        $this->logIn('ROLE_ADMIN');
        $client = static::createClient();
        $crawler = $client->request('GET', '/ru/admin/districts');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }*/

    public function testDistrictShow()
    {
        $this->logIn('ROLE_ADMIN');
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $slug = $em
            ->getRepository('AppBundle:District')
            ->findOneBy([])->getSlug();
        /*$client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));*/
        $client = static::createClient();
        $crawler = $client->request('GET', "/ru/admin/district/show/{$slug}");
        //$crawler = $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }
}