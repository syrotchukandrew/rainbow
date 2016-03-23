<?php
namespace AppBundle\Tests\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BaseTestController extends WebTestCase
{
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function logIn($role)
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $session = $this->client->getContainer()->get('session');
        $admin = $em
            ->getRepository('AppBundle:User')
            ->findByRole($role);
        $firewall = 'main';
        $token = new UsernamePasswordToken($admin[0]->getUsername(), null, $firewall, array($role));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}