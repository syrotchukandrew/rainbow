<?php
namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BaseTestController extends WebTestCase
{
    /** @var Client */
    protected $client = null;

        public function setUp()
        {
            $this->client = static::createClient();
            //$this->runCommand(['command' => 'doctrine:database:create']);
            //$this->runCommand(['command' => 'doctrine:schema:update', '--force' => true]);
            //$this->runCommand(['command' => 'doctrine:fixtures:load', '--no-interaction' => true]);
        }

    protected function logIn($role)
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $session = $client->getContainer()->get('session');
        $admin = $em
            ->getRepository('AppBundle:User')
            ->findByRole($role);
        $firewall = 'main';
        $token = new UsernamePasswordToken($admin[0]->getUsername(), null, $firewall, array($role));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /*public function tearDown()
    {
        $this->runCommand(['command' => 'doctrine:database:drop', '--force' => true]);
        $this->client = null;
    }*/

    /*protected function runCommand( array $arguments = [] )
    {
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);
        $arguments['--quiet'] = true;
        $arguments['-e'] = 'test';
        $input = new ArrayInput($arguments);
        $application->run($input, new ConsoleOutput());
    }*/
}

