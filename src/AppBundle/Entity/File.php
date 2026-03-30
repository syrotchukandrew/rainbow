<?php

namespace AppBundle\Entity;

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
    private $id;

    #[ORM\ManyToOne(targetEntity: 'AppBundle\Entity\Estate', inversedBy: 'files', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'estate_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $estate;

    #[ORM\Column]
    #[Gedmo\UploadableFilePath]
    private $path;

    #[ORM\Column]
    #[Gedmo\UploadableFileName]
    private $name;

    #[ORM\Column]
    #[Gedmo\UploadableFileMimeType]
    private $mimeType;

    #[Assert\File(maxSize: '5M', maxSizeMessage: 'file.size')]
    #[ORM\Column(type: 'decimal')]
    #[Gedmo\UploadableFileSize]
    private $size;

    public function getId()
    {
        return $this->id;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setEstate(\AppBundle\Entity\Estate $estate = null)
    {
        $this->estate = $estate;

        return $this;
    }

    public function getEstate()
    {
        return $this->estate;
    }
}
