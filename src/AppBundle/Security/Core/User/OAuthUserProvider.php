<?php

namespace AppBundle\Security\Core\User;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $socialId = $response->getUsername();
        $service = $response->getResourceOwner()->getName();
        $propertyMap = [
            'facebook'  => 'facebookId',
            'google'    => 'googleId',
            'vkontakte' => 'vkontakteId',
        ];
        $property = $propertyMap[$service] ?? null;

        $user = $property
            ? $this->em->getRepository(User::class)->findOneBy([$property => $socialId])
            : null;

        if (null === $user) {
            $email = $response->getEmail();
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (null === $user) {
                $user = new User();
                $username = $service === 'vkontakte'
                    ? $response->getLastName().' '.$response->getFirstName()
                    : ($response->getNickname() ?? $email);
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setEnabled(true);
                $user->setRoles(['ROLE_USER']);
                $user->setPassword(bin2hex(random_bytes(32)));
            }

            switch ($service) {
                case 'google':    $user->setGoogleId($socialId); break;
                case 'facebook':  $user->setFacebookId($socialId); break;
                case 'vkontakte': $user->setVkontakteId($socialId); break;
            }

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $identifier])
            ?? $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (null === $user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $refreshed = $this->em->getRepository(User::class)->find($user->getId());
        if (null === $refreshed) {
            throw new UserNotFoundException(sprintf('User with id "%s" not found.', $user->getId()));
        }
        return $refreshed;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
