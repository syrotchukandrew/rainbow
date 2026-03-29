<?php

namespace AppBundle\DataFixtures\ORM\Dev;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user_user = new User();
            $user_user->setUsername('user_user'.$i);
            $user_user->setEmail('user_user'.$i.'@rainbow.com');
            $user_user->setRoles(['ROLE_USER']);
            $user_user->setEnabled(true);
            $user_user->setPassword($this->passwordHasher->hashPassword($user_user, 'qweasz'));
            $manager->persist($user_user);

            $user_manager = new User();
            $user_manager->setUsername('user_manager'.$i);
            $user_manager->setEmail('user_manager'.$i.'@rainbow.com');
            $user_manager->setRoles(['ROLE_MANAGER']);
            $user_manager->setEnabled(true);
            $user_manager->setPassword($this->passwordHasher->hashPassword($user_manager, 'qweasz'));
            $manager->persist($user_manager);
        }

        $user_admin = new User();
        $user_admin->setUsername('user_admin');
        $user_admin->setEmail('qweasz@ukr.net');
        $user_admin->setRoles(['ROLE_ADMIN']);
        $user_admin->setEnabled(true);
        $user_admin->setPassword($this->passwordHasher->hashPassword($user_admin, 'qweasz'));
        $manager->persist($user_admin);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
