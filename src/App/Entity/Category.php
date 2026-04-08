<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Estate;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'categories')]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[Gedmo\Translatable]
    #[ORM\Column(name: 'title', type: 'string', length: 64)]
    #[Assert\NotBlank(message: 'category.blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'category.title.too_short',
        maxMessage: 'category.title.too_long'
    )]
    private ?string $title = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer')]
    private ?int $lft = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer')]
    private ?int $lvl = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer')]
    private ?int $rgt = null;

    #[Gedmo\TreeRoot]
    #[ORM\Column(name: 'root', type: 'integer', nullable: true)]
    private ?int $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Category $parent = null;

    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private Collection $children;

    #[Gedmo\Translatable]
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 128)]
    private ?string $slug = null;

    #[ORM\OneToMany(targetEntity: Estate::class, mappedBy: 'category')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $estates;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->estates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setParent(?Category $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setLft(?int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setLvl(?int $lvl): static
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setRgt(?int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setRoot(?int $root): static
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot(): ?int
    {
        return $this->root;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function addChild(Category $children): static
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChild(Category $children): void
    {
        $this->children->removeElement($children);
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addEstate(Estate $estates): static
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
}
