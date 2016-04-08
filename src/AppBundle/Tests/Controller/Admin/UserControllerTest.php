<?php

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class UserControllerTest extends BaseTestController
{
    public function testRegularUsersCannotAccessToTheBackend()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_user',
            'PHP_AUTH_PW'   => 'qweasz',
        ));

        $client->request('GET', '/ru/admin/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdministratorUsersCanAccessToTheBackend()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $client->request('GET', '/ru/admin/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUsers()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/ru/admin/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testUsersManagers()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[1];
        $crawler = $client->request('GET', "/ru/admin/estates/{$user->getUsername()}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(
            20,
            $crawler->filter('h2')
        );
    }

    public function testLockUnlockUser()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $status = $user->isAccountNonLocked();

        $this->assertEquals(true, $status);
        $client->request('GET', "/ru/admin/users/lock_user/{$user->getUsername()}");
        $status = $user->isAccountNonLocked();
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $status);
        $client->request('GET', "/ru/admin/users/unlock_user/{$user->getUsername()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $status);

        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager1',
            'PHP_AUTH_PW'   => 'qweasz',
        ));        $client->request('GET', "/ru/admin/users/lock_user/{$user->getUsername()}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $client->request('GET', "/ru/admin/users/unlock_user/{$user->getUsername()}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDoUser()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));

        $client->request('GET', "/ru/admin/users/do_user/{$user->getUsername()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));
    }

    public function testDoManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery(
                'SELECT u FROM AppBundle:User u
             WHERE NOT u.roles LIKE :role2
             AND NOT u.roles LIKE :role3')
            ->setParameter('role2', '%ROLE_ADMIN%')
            ->setParameter('role3', '%ROLE_MANAGER%');
        $users = $query->getResult();

        $user = $users[0];
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));
        $client->request('GET', "/ru/admin/users/do_manager/{$user->getUsername()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));
    }
}
