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
        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', '/ru/admin/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUsers()
    {
        $this->logIn('ROLE_ADMIN');
        $crawler = $this->client->request('GET', '/ru/admin/users');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('h1')
        );
    }

    public function testUsersManagers()
    {
        $this->logIn('ROLE_ADMIN');
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $crawler = $this->client->request('GET', "/ru/admin/estates/{$user->getUsername()}");

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(
            20,
            $crawler->filter('h2')
        );
    }

    public function testLockUnlockUser()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $status = $user->isAccountNonLocked();

        $this->assertEquals(true, $status);

        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', "/ru/admin/users/lock_user/{$user->getUsername()}");
        $status = $user->isAccountNonLocked();
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(false, $status);
        $this->client->request('GET', "/ru/admin/users/unlock_user/{$user->getUsername()}");
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(false, $status);

        $this->logIn('ROLE_MANAGER');
        $this->client->request('GET', "/ru/admin/users/lock_user/{$user->getUsername()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', "/ru/admin/users/unlock_user/{$user->getUsername()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDoUser()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em
            ->getRepository('AppBundle:User')
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));

        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', "/ru/admin/users/do_user/{$user->getUsername()}");
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));
    }

    public function testDoManager()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery(
                'SELECT u FROM AppBundle:User u
             WHERE NOT u.roles LIKE :role2
             AND NOT u.roles LIKE :role3')
            ->setParameter('role2', '%ROLE_ADMIN%')
            ->setParameter('role3', '%ROLE_MANAGER%');
        $users = $query->getResult();

        $user = $users[0];
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));

        $this->logIn('ROLE_ADMIN');
        $this->client->request('GET', "/ru/admin/users/do_manager/{$user->getUsername()}");
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));
    }
}
