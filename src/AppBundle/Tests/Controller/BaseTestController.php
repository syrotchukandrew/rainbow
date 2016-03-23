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
    protected function logIn()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $session = $this->client->getContainer()->get('session');
        $admin = $em
            ->getRepository('AppBundle:User')
            ->findBy(['role' => User::ROLE_ADMIN]);
        $firewall = 'main';
        $token = new UsernamePasswordToken($admin[0], null, $firewall, array(User::ROLE_ADMIN));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}