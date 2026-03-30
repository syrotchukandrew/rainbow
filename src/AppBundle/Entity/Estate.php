<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'estate.title.blank')]
    #[Assert\Length(
        min: 3,
        max: 200,
        minMessage: 'estate.title.too_short',
        maxMessage: 'estate.title.too_long'
    )]
    private ?string $title = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: 'string', length: 255, unique: true)]
    private ?string $slug = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\Column(name: 'createdBy', type: 'string', length: 255)]
    private ?string $createdBy = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updatedAt', type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: 'description', type: 'text')]
    #[Assert\NotBlank(message: 'estate.description.blank')]
    #[Assert\Length(
        min: 3,
        max: 5000,
        minMessage: 'estate.description.too_short',
        maxMessage: 'estate.description.too_long'
    )]
    private ?string $description = null;

    #[ORM\Column(name: 'price', type: 'integer', nullable: true)]
    private ?int $price = null;

    #[ORM\Column(name: 'floor', type: 'array', nullable: true)]
    private mixed $floor = null;

    #[ORM\Column(name: 'first_last_floor', type: 'boolean', nullable: true)]
    private ?bool $firstLastFloor = null;

    #[ORM\Column(name: 'exclusive', type: 'boolean')]
    private bool $exclusive;

    #[ORM\ManyToOne(targetEntity: 'District', inversedBy: 'estates')]
    #[ORM\JoinColumn(name: 'district_id', referencedColumnName: 'id', nullable: false)]
    private ?District $district = null;

    #[ORM\OneToMany(targetEntity: 'AppBundle\Entity\File', mappedBy: 'estate', cascade: ['remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToOne(targetEntity: 'AppBundle\Entity\File')]
    private ?\AppBundle\Entity\File $mainFoto = null;

    private mixed $imageFile = null;

    #[ORM\OneToMany(targetEntity: 'Comment', mappedBy: 'estate', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\ManyToOne(targetEntity: 'Category', inversedBy: 'estates')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->exclusive = false;
    }

    public function setImageFile(mixed $imageFile): static
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getImageFile(): mixed
    {
        return $this->imageFile;
    }

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

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
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

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setFloor(mixed $floor): static
    {
        $this->floor = $floor;

        return $this;
    }

    public function getFloor(): mixed
    {
        return $this->floor;
    }

    public function isFirstLastFloor(): ?bool
    {
        return $this->firstLastFloor;
    }

    public function setFirstLastFloor(?bool $firstLastFloor): void
    {
        $this->firstLastFloor = $firstLastFloor;
    }

    public function setExclusive(bool $exclusive): static
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    public function setDistrict(?\AppBundle\Entity\District $district = null): static
    {
        $this->district = $district;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function addFile(\AppBundle\Entity\File $files): static
    {
        $this->files[] = $files;

        return $this;
    }

    public function removeFile(\AppBundle\Entity\File $files): void
    {
        $this->files->removeElement($files);
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addComment(\AppBundle\Entity\Comment $comments): static
    {
        $this->comments[] = $comments;

        return $this;
    }

    public function removeComment(\AppBundle\Entity\Comment $comments): void
    {
        $this->comments->removeElement($comments);
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function setCategory(?\AppBundle\Entity\Category $category = null): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setMainFoto(?\AppBundle\Entity\File $mainFoto): static
    {
        $this->mainFoto = $mainFoto;

        return $this;
    }

    public function getMainFoto(): ?\AppBundle\Entity\File
    {
        return $this->mainFoto;
    }
}
