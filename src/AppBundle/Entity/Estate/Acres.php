<?php
namespace AppBundle\Entity\Estate;

use Doctrine\ORM\Mapping as ORM;


/**
 *   @ORM\Entity
 */

class Acres extends Estate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="volume1", type="integer")
     */
    private $volume1;






    /**
     * Set volume1
     *
     * @param integer $volume1
     * @return Acres
     */
    public function setVolume1($volume1)
    {
        $this->volume1 = $volume1;

        return $this;
    }

    /**
     * Get volume1
     *
     * @return integer 
     */
    public function getVolume1()
    {
        return $this->volume1;
    }
}
