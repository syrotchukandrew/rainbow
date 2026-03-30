<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'district')]
#[ORM\Entity(repositoryClass: 'AppBundle\Repository\DistrictRepository')]
class District
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 255, unique: true)]
    private $slug;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'district.blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'district.too_short',
        maxMessage: 'district.too_long'
    )]
    private $title;

    #[ORM\OneToMany(targetEntity: 'Estate', mappedBy: 'district')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private $estates;

    public function __construct()
    {
        $this->estates = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
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
