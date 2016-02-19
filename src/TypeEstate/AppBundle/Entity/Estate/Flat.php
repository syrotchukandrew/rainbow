<?php

namespace AppBundle\Entity\Estate;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flat
 */
class Flat
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $volume;


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
     * Set volume
     *
     * @param integer $volume
     * @return Flat
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }
}
