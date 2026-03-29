<?php
namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class BaseTestController extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client = null;

        public function setUp(): void
        {
        }

    protected function logIn($role): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(\AppBundle\Entity\User::class)->findByRole($role)[0];
        $client->loginUser($user);
        return $client;
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

