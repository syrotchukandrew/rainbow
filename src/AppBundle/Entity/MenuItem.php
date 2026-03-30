<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'menu_item')]
#[ORM\Entity(repositoryClass: 'AppBundle\Repository\MenuItemRepository')]
class MenuItem
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'menu_item.title.blank')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'menu_item.title.too_short',
        maxMessage: 'menu_item.title.too_long'
    )]
    private $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'menu_item.description.blank')]
    private $description;

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
