<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @Gedmo\Uploadable(path="images/estates/", filenameGenerator="SHA1", allowOverwrite=true, appendNumber=true)
 */

class File
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Estate", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(name="estate_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $estate;

    /**
     * @ORM\Column
     * @Gedmo\UploadableFilePath
     */

    private $path;

    /**
     * @ORM\Column
     * @Gedmo\UploadableFileName
     */
    private $name;

    /**
     * @ORM\Column
     * @Gedmo\UploadableFileMimeType
     */
    private $mimeType;

    /**
     * @Assert\File(maxSize="5M", maxSizeMessage="file.size")
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     */
    private $size;

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
     * Set path
     *
     * @param string $path
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return File
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set estate
     *
     * @param \AppBundle\Entity\Estate $estate
     * @return File
     */
    public function setEstate(\AppBundle\Entity\Estate $estate = null)
    {
        $this->estate = $estate;

        return $this;
    }

    /**
     * Get estate
     *
     * @return \AppBundle\Entity\Estate 
     */
    public function getEstate()
    {
        return $this->estate;
    }
}
