<?php

namespace Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Gedmo\Uploadable(path="images/estates/", callback="myCallbackMethod", filenameGenerator="SHA1",
 *      allowOverwrite=true, appendNumber=true)
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Estate", inversedBy="files")
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
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     */
    private $size;


    public function myCallbackMethod(array $info)
    {
        // Do some stuff with the file..
    }


}