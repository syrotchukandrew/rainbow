<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestController extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client = null;

        public function setUp(): void
        {
        }

    protected function logIn($role): KernelBrowser
    {
        $client = static::createClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findByRole($role)[0];
        $client->loginUser($user);
        return $client;
    }
}
