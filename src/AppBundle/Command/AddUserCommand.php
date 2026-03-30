<?php

declare(strict_types=1);

namespace AppBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use AppBundle\Entity\User;

/**
 *     $ php bin/console app:add-user --is-admin
 *     $ php bin/console app:add-user
 *     $ php bin/console app:add-user --help
 */
#[AsCommand(name: 'app:add-user', description: 'Creates users and stores them in the database')]
class AddUserCommand extends Command
{
    private const MAX_ATTEMPTS = 5;

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addOption('is-admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('is-admin');
        $existingUser = $this->entityManager->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        if (null !== $existingUser) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }
        $existingEmail = $this->entityManager->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('email' => $email));
        if (null !== $existingEmail) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(array($isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER'));
        $user->setEnabled(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('');
        $output->writeln(sprintf('[OK] %s was successfully created: %s (%s)', $isAdmin ? 'Administrator user' : 'User', $user->getUserIdentifier(), $user->getEmail()));
        if ($output->isVerbose()) {
            $finishTime = microtime(true);
            $elapsedTime = $finishTime - $startTime;
            $output->writeln(sprintf('[INFO] New user database id: %d / Elapsed time: %.2f ms', $user->getId(), $elapsedTime*1000));
        }

        return Command::SUCCESS;
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
