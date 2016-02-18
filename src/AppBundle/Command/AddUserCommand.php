<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

/**
 *     $ php app/console app:add-user --is-admin
 *     $ php app/console app:add-user
 *     $ php app/console app:add-user --help
 */
class AddUserCommand extends ContainerAwareCommand
{
    const MAX_ATTEMPTS = 5;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:add-user')
            ->setDescription('Creates users and stores them in the database')
            ->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addOption('is-admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('username') && null !== $input->getArgument('password') && null !== $input->getArgument('email')) {
            return;
        }
        $output->writeln('');
        $output->writeln('Add User Command Interactive Wizard');
        $output->writeln('-----------------------------------');
        $output->writeln(array(
            '',
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php app/console app:add-user username password email@example.com',
            '',
        ));
        $output->writeln(array(
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
            '',
        ));
        $console = $this->getHelper('question');
        $username = $input->getArgument('username');
        if (null === $username) {
            $question = new Question(' > <info>Username</info>: ');
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('The username cannot be empty');
                }
                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);
            $username = $console->ask($input, $output, $question);
            $input->setArgument('username', $username);
        } else {
            $output->writeln(' > <info>Username</info>: '.$username);
        }
        $password = $input->getArgument('password');
        if (null === $password) {
            $question = new Question(' > <info>Password</info> (your type will be hidden): ');
            $question->setValidator(array($this, 'passwordValidator'));
            $question->setHidden(true);
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $password = $console->ask($input, $output, $question);
            $input->setArgument('password', $password);
        } else {
            $output->writeln(' > <info>Password</info>: '.str_repeat('*', strlen($password)));
        }
        $email = $input->getArgument('email');
        if (null === $email) {
            $question = new Question(' > <info>Email</info>: ');
            $question->setValidator(array($this, 'emailValidator'));
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $email = $console->ask($input, $output, $question);
            $input->setArgument('email', $email);
        } else {
            $output->writeln(' > <info>Email</info>: '.$email);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('is-admin');
        $existingUser = $this->entityManager->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
        if (null !== $existingUser) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }
        $existingEmail = $this->entityManager->getRepository('AppBundle:User')->findOneBy(array('email' => $email));
        if (null !== $existingEmail) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(array($isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'));
        $user->setEnabled(true);
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('');
        $output->writeln(sprintf('[OK] %s was successfully created: %s (%s)', $isAdmin ? 'Administrator user' : 'User', $user->getUsername(), $user->getEmail()));
        if ($output->getVerbosity()) {
            $finishTime = microtime(true);
            $elapsedTime = $finishTime - $startTime;
            $output->writeln(sprintf('[INFO] New user database id: %d / Elapsed time: %.2f ms', $user->getId(), $elapsedTime*1000));
        }
    }

    /**
     * @internal
     */
    public function passwordValidator($plainPassword)
    {
        if (empty($plainPassword)) {
            throw new \Exception('The password can not be empty');
        }
        if (strlen(trim($plainPassword)) < 6) {
            throw new \Exception('The password must be at least 6 characters long');
        }
        return $plainPassword;
    }

    /**
     * @internal
     */
    public function emailValidator($email)
    {
        if (empty($email)) {
            throw new \Exception('The email can not be empty');
        }
        if (false === strpos($email, '@')) {
            throw new \Exception('The email should look like a real email');
        }
        return $email;
    }

    private function getCommandHelp()
    {
        return <<<HELP
The <info>%command.name%</info> command creates new users and saves them in the database:

  <info>php %command.full_name%</info> <comment>username password email</comment>

By default the command creates regular users. To create administrator users,
add the <comment>--is-admin</comment> option:

  <info>php %command.full_name%</info> username password email <comment>--is-admin</comment>

If you omit any of the three required arguments, the command will ask you to
provide the missing values:

  # command will ask you for the email
  <info>php %command.full_name%</info> <comment>username password</comment>

  # command will ask you for the email and password
  <info>php %command.full_name%</info> <comment>username</comment>

  # command will ask you for all arguments
  <info>php %command.full_name%</info>
HELP;
    }
}
