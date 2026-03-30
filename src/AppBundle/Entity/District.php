<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $id = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'district.blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'district.too_short',
        maxMessage: 'district.too_long'
    )]
    private ?string $title = null;

    #[ORM\OneToMany(targetEntity: 'Estate', mappedBy: 'district')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $estates;

    public function __construct()
    {
        $this->estates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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

    public function addEstate(\AppBundle\Entity\Estate $estates): static
    {
        $this->estates[] = $estates;

        return $this;
    }

    public function removeEstate(\AppBundle\Entity\Estate $estates): void
    {
        $this->estates->removeElement($estates);
    }

    public function getEstates(): Collection
    {
        return $this->estates;
    }
}
