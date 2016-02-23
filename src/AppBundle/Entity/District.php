<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Estate as Estate;

/**
 * District
 *
 * @ORM\Table(name="district")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DistrictRepository")
 */
class District
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
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var array
     * @ORM\OneToMany(targetEntity="Estate", mappedBy="district")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $estates;

    public function __construct()
    {
        $this->estates = new ArrayCollection();
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
     * Set slug
     *
     * @param string $slug
     * @return District
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
     * Set title
     *
     * @param string $title
     * @return District
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
     * Add estates
     *
     * @param \AppBundle\Entity\Estate $estates
     * @return District
     */
    public function addEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates[] = $estates;

        return $this;
    }

    /**
     * Remove estates
     *
     * @param \AppBundle\Entity\Estate $estates
     */
    public function removeEstate(\AppBundle\Entity\Estate $estates)
    {
        $this->estates->removeElement($estates);
    }

    /**
     * Get estates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEstates()
    {
        return $this->estates;
    }
}
