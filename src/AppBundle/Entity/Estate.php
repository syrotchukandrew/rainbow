<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'estate')]
#[ORM\Entity(repositoryClass: 'AppBundle\Repository\EstateRepository')]
class Estate
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'estate.title.blank')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'estate.title.too_short',
        maxMessage: 'estate.title.too_long'
    )]
    private $title;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 255, unique: true)]
    private $slug;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\Column(name: 'createdBy', type: 'string', length: 255)]
    private $createdBy;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    private $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updatedAt', type: 'datetime')]
    private $updatedAt;

    #[ORM\Column(name: 'description', type: 'text')]
    #[Assert\NotBlank(message: 'estate.description.blank')]
    #[Assert\Length(
        min: 3,
        max: 5000,
        minMessage: 'estate.description.too_short',
        maxMessage: 'estate.description.too_long'
    )]
    private $description;

    #[ORM\Column(name: 'price', type: 'integer', nullable: true)]
    private $price;

    #[ORM\Column(name: 'floor', type: 'array', nullable: true)]
    private $floor;

    #[ORM\Column(name: 'first_last_floor', type: 'boolean', nullable: true)]
    private $firstLastFloor;

    #[ORM\Column(name: 'exclusive', type: 'boolean')]
    private $exclusive;

    #[ORM\ManyToOne(targetEntity: 'District', inversedBy: 'estates')]
    #[ORM\JoinColumn(name: 'district_id', referencedColumnName: 'id', nullable: false)]
    private $district;

    #[ORM\OneToMany(targetEntity: 'AppBundle\Entity\File', mappedBy: 'estate', cascade: ['remove'], orphanRemoval: true)]
    private $files;

    #[ORM\OneToOne(targetEntity: 'AppBundle\Entity\File')]
    private $mainFoto;

    private $imageFile;

    #[ORM\OneToMany(targetEntity: 'Comment', mappedBy: 'estate', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private $comments;

    #[ORM\ManyToOne(targetEntity: 'Category', inversedBy: 'estates')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private $category;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->exclusive = false;
    }

    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

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

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
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

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    public function getFloor()
    {
        return $this->floor;
    }

    public function isFirstLastFloor()
    {
        return $this->firstLastFloor;
    }

    public function setFirstLastFloor($firstLastFloor)
    {
        $this->firstLastFloor = $firstLastFloor;
    }

    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function isExclusive()
    {
        return $this->exclusive;
    }

    public function setDistrict(\AppBundle\Entity\District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    public function getDistrict()
    {
        return $this->district;
    }

    public function addFile(\AppBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    public function removeFile(\AppBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function addComment(\AppBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    public function removeComment(\AppBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setMainFoto($mainFoto)
    {
        $this->mainFoto = $mainFoto;

        return $this;
    }

    public function getMainFoto()
    {
        return $this->mainFoto;
    }
}
