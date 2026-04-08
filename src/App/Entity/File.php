<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Estate;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[Gedmo\Uploadable(path: 'images/estates/', filenameGenerator: 'SHA1', allowOverwrite: true, appendNumber: true)]
class File
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Estate::class, inversedBy: 'files', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'estate_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Estate $estate = null;

    #[ORM\Column]
    #[Gedmo\UploadableFilePath]
    private ?string $path = null;

    #[ORM\Column]
    #[Gedmo\UploadableFileName]
    private ?string $name = null;

    #[ORM\Column]
    #[Gedmo\UploadableFileMimeType]
    private ?string $mimeType = null;

    #[Assert\File(maxSize: '5M', maxSizeMessage: 'file.size')]
    #[ORM\Column(type: 'decimal')]
    #[Gedmo\UploadableFileSize]
    private ?string $size = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setEstate(?Estate $estate = null): static
    {
        $this->estate = $estate;

        return $this;
    }

    public function getEstate(): ?Estate
    {
        return $this->estate;
    }
}
