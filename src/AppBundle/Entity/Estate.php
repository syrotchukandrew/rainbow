<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Estate
 *
 * @ORM\Table(name="estate")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EstateRepository")
 */
class Estate
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"})
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="createdBy", type="string", length=255)
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="RentOrSell", type="boolean")
     */
    private $rentOrSell;

    /**
     * @var array
     *
     * @ORM\Column(name="floor", type="array", nullable=true)
     */
    private $floor;

    /**
     * @var bool
     *
     * @ORM\Column(name="exclusive", type="boolean")
     */
    private $exclusive;

    /**
     * @ORM\ManyToOne(targetEntity="District", inversedBy="estates")
     * @ORM\JoinColumn(name="district_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $district;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\File", mappedBy="estate", orphanRemoval=true)
     */
    private $files;

    /**
     * @var array
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="estate")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="estates")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->files = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Estate
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Estate
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     * @return Estate
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Estate
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Estate
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Estate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Estate
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set rentOrSell
     *
     * @param boolean $rentOrSell
     * @return Estate
     */
    public function setRentOrSell($rentOrSell)
    {
        $this->rentOrSell = $rentOrSell;

        return $this;
    }

    /**
     * Get rentOrSell
     *
     * @return boolean
     */
    public function getRentOrSell()
    {
        return $this->rentOrSell;
    }

    /**
     * Set floor
     *
     * @param array $floor
     * @return Estate
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor
     *
     * @return array
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * Set exclusive
     *
     * @param boolean $exclusive
     * @return Estate
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    /**
     * Get exclusive
     *
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * Set district
     *
     * @param \AppBundle\Entity\District $district
     * @return Estate
     */
    public function setDistrict(\AppBundle\Entity\District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return \AppBundle\Entity\District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Add files
     *
     * @param \AppBundle\Entity\File $files
     * @return Estate
     */
    public function addFile(\AppBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \AppBundle\Entity\File $files
     */
    public function removeFile(\AppBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add comments
     *
     * @param \AppBundle\Entity\Comment $comments
     * @return Estate
     */
    public function addComment(\AppBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \AppBundle\Entity\Comment $comments
     */
    public function removeComment(\AppBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     * @return Estate
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
