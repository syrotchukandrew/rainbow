<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class DistrictControllerTest extends BaseTestController
{
    public function testDistricts()
    {
        $this->logIn('ROLE_ADMIN');
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/admin/districts/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }
}