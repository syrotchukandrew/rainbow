<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'AppBundle\Repository\UserRepository')]
#[ORM\Table(name: 'fos_user')]
#[UniqueEntity(fields: 'email', message: 'Email already taken')]
#[UniqueEntity(fields: 'username', message: 'Username already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected $username;

    #[ORM\Column(name: 'username_canonical', type: 'string', length: 255, unique: true)]
    protected $usernameCanonical;

    #[ORM\Column(type: 'string', length: 255)]
    protected $email;

    #[ORM\Column(name: 'email_canonical', type: 'string', length: 255, unique: true)]
    protected $emailCanonical;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $salt;

    #[ORM\Column(type: 'string', length: 255)]
    protected $password;

    protected $plainPassword;

    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    protected $lastLogin;

    #[ORM\Column(type: 'boolean')]
    protected $locked = false;

    #[ORM\Column(type: 'boolean')]
    protected $expired = false;

    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    protected $expiresAt;

    #[ORM\Column(name: 'confirmation_token', type: 'string', length: 255, nullable: true)]
    protected $confirmationToken;

    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    protected $passwordRequestedAt;

    #[ORM\Column(type: 'json')]
    protected $roles = [];

    #[ORM\Column(name: 'credentials_expired', type: 'boolean')]
    protected $credentialsExpired = false;

    #[ORM\Column(name: 'credentials_expire_at', type: 'datetime', nullable: true)]
    protected $credentialsExpireAt;

    #[ORM\ManyToMany(targetEntity: 'AppBundle\Entity\Estate')]
    #[ORM\JoinTable(
        name: 'users_estates',
        joinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'estate_id', referencedColumnName: 'id')]
    )]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private $estates;

    #[ORM\Column(name: 'facebook_id', type: 'string', length: 255, nullable: true)]
    protected $facebookId;

    #[ORM\Column(name: 'google_id', type: 'string', length: 255, nullable: true)]
    protected $googleId;

    #[ORM\Column(name: 'vkontakte_id', type: 'string', length: 255, nullable: true)]
    protected $vkontakteId;

    public function __construct()
    {
        $this->estates = new ArrayCollection();
        $this->roles = [];
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->credentialsExpired = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        $this->usernameCanonical = mb_strtolower($username);
        return $this;
    }

    public function getUsernameCanonical(): ?string
    {
        return $this->usernameCanonical;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->emailCanonical = mb_strtolower($email);
        return $this;
    }

    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAccountNonLocked(): bool
    {
        return !$this->locked;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_values(array_filter($this->roles, fn($r) => $r !== strtoupper($role)));
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }
        return $this->password === $user->getPassword()
            && $this->enabled === $user->isEnabled()
            && $this->username === $user->getUserIdentifier();
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function addEstate(Estate $estates): self
    {
        $this->estates[] = $estates;
        return $this;
    }

    public function removeEstate(Estate $estates): void
    {
        $this->estates->removeElement($estates);
    }

    public function getEstates(): Collection
    {
        return $this->estates;
    }

    public function hasEstate(Estate $estate): bool
    {
        foreach ($this->getEstates() as $item) {
            if ($item->getId() === $estate->getId()) {
                return true;
            }
        }
        return false;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setVkontakteId(?string $vkontakteId): self
    {
        $this->vkontakteId = $vkontakteId;
        return $this;
    }

    public function getVkontakteId(): ?string
    {
        return $this->vkontakteId;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $token): self
    {
        $this->confirmationToken = $token;
        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $date): self
    {
        $this->passwordRequestedAt = $date;
        return $this;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->passwordRequestedAt instanceof \DateTimeInterface
            && $this->passwordRequestedAt->getTimestamp() + $ttl > time();
    }
}
