<?php


namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\User;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');

        $user_user = new User();
        $user_user->setUsername('user_user');
        $user_user->setEmail('user_user@rainbow.com');
        $user_user->setRoles(array('ROLE_USER'));
        $user_user->setEnabled(true);
        $encodedPassword = $passwordEncoder->encodePassword($user_user, 'qweasz');
        $user_user->setPassword($encodedPassword);
        $manager->persist($user_user);

        $user_admin = new User();
        $user_admin->setUsername('user_admin');
        $user_admin->setEmail('user_admin@rainbow.com');
        $user_admin->setRoles(array('ROLE_ADMIN'));
        $user_admin->setEnabled(true);
        $encodedPassword = $passwordEncoder->encodePassword($user_admin, 'qweasz');
        $user_admin->setPassword($encodedPassword);
        $manager->persist($user_admin);

        $user_manager = new User();
        $user_manager->setUsername('user_manager');
        $user_manager->setEmail('user_manager@rainbow.com');
        $user_manager->setRoles(array('ROLE_MANAGER'));
        $user_manager->setEnabled(true);
        $encodedPassword = $passwordEncoder->encodePassword($user_manager, 'qweasz');
        $user_manager->setPassword($encodedPassword);
        $manager->persist($user_manager);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}