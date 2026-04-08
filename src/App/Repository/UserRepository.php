<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('user')
            ->where('user.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
            ->getQuery()
            ->getResult();
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $identifier)
            ->setParameter('email', $identifier)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $user) {
            throw new UserNotFoundException(sprintf(
                'Unable to find an active App\Entity\User identified by "%s".',
                $identifier
            ));
        }

        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
