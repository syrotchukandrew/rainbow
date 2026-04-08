<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'menu_item')]
#[ORM\Entity(repositoryClass: 'App\Repository\MenuItemRepository')]
class MenuItem
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'menu_item.title.blank')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'menu_item.title.too_short',
        maxMessage: 'menu_item.title.too_long'
    )]
    private ?string $title = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'menu_item.description.blank')]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
