<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'categories')]
#[ORM\Entity(repositoryClass: 'AppBundle\Repository\CategoryRepository')]
class Category
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    #[Gedmo\Translatable]
    #[ORM\Column(name: 'title', type: 'string', length: 64)]
    #[Assert\NotBlank(message: 'category.blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'category.title.too_short',
        maxMessage: 'category.title.too_long'
    )]
    private $title;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer')]
    private $lft;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer')]
    private $lvl;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer')]
    private $rgt;

    #[Gedmo\TreeRoot]
    #[ORM\Column(name: 'root', type: 'integer', nullable: true)]
    private $root;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'Category', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    #[ORM\OneToMany(targetEntity: 'Category', mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;

    #[Gedmo\Translatable]
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 128)]
    private $slug;

    #[ORM\OneToMany(targetEntity: 'AppBundle\Entity\Estate', mappedBy: 'category')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private $estates;

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->estates = new ArrayCollection();
    }

    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function addChild(\AppBundle\Entity\Category $children)
    {
        $this->children[] = $children;

        return $this;
    }

    public function removeChild(\AppBundle\Entity\Category $children)
    {
        $this->children->removeElement($children);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates[] = $estates;

        return $this;
    }

    public function removeEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates->removeElement($estates);
    }

    public function getEstates()
    {
        return $this->estates;
    }
}
