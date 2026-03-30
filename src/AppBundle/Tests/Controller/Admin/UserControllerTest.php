<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Admin;

use AppBundle\Tests\Controller\BaseTestController;

class UserControllerTest extends BaseTestController
{
    public function testRegularUsersCannotAccessToTheBackend()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_user1',
            'PHP_AUTH_PW'   => 'qweasz',
        ));

        $client->request('GET', '/uk/admin');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdministratorUsersCanAccessToTheBackend()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $client->request('GET', '/uk/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUsers()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $crawler = $client->request('GET', '/uk/admin/users');

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
            ->getRepository(\AppBundle\Entity\User::class)
            ->findByRole('ROLE_MANAGER');
        $user = $users[1];
        $crawler = $client->request('GET', "/uk/admin/estates/{$user->getUserIdentifier()}");

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
            ->getRepository(\AppBundle\Entity\User::class)
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $userId = $user->getId();
        $username = $user->getUserIdentifier();

        $this->assertEquals(true, $user->isEnabled());
        $client->request('GET', "/uk/admin/users/lock_user/{$username}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $em->clear();
        $user = $em->getRepository(\AppBundle\Entity\User::class)->find($userId);
        $this->assertEquals(false, $user->isEnabled());
        $client->request('GET', "/uk/admin/users/unlock_user/{$username}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $em->clear();
        $user = $em->getRepository(\AppBundle\Entity\User::class)->find($userId);
        $this->assertEquals(true, $user->isEnabled());

        static::ensureKernelShutdown();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_manager1',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $client->request('GET', "/uk/admin/users/lock_user/{$user->getUserIdentifier()}");
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $client->request('GET', "/uk/admin/users/unlock_user/{$user->getUserIdentifier()}");
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
            ->getRepository(\AppBundle\Entity\User::class)
            ->findByRole('ROLE_MANAGER');
        $user = $users[0];
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));

        $client->request('GET', "/uk/admin/users/do_user/{$user->getUserIdentifier()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));
    }

    public function testDoManager()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user_admin',
            'PHP_AUTH_PW'   => 'qweasz',
        ));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery(
                'SELECT u FROM AppBundle\Entity\User u
             WHERE NOT u.roles LIKE :role2
             AND NOT u.roles LIKE :role3')
            ->setParameter('role2', '%ROLE_ADMIN%')
            ->setParameter('role3', '%ROLE_MANAGER%');
        $users = $query->getResult();

        $user = $users[0];
        $this->assertEquals(false, $user->hasRole('ROLE_MANAGER'));
        $client->request('GET', "/uk/admin/users/do_manager/{$user->getUserIdentifier()}");
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(true, $user->hasRole('ROLE_MANAGER'));
    }
}
