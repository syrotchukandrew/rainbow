<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Estate;
use App\Repository\CommentRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'comment')]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Estate::class, inversedBy: 'comments', cascade: ['persist'])]
    private ?Estate $estate = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'comment.blank')]
    #[Assert\Length(
        min: 5,
        minMessage: 'comment.too_short',
        max: 10000,
        maxMessage: 'comment.too_long'
    )]
    private ?string $content = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\Column]
    private ?string $createdBy = null;

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    private bool $enabled = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $boolean): static
    {
        $this->enabled = $boolean;

        return $this;
    }

    public function isAuthor(User $user): bool
    {
        return $user->getUserIdentifier() == $this->getCreatedBy();
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setEstate(?Estate $estate = null): static
    {
        $this->estate = $estate;

        return $this;
    }

    public function getEstate(): ?Estate
    {
        return $this->estate;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
