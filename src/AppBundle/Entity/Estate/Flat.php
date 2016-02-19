<?php
namespace AppBundle\Entity\Estate;

use Doctrine\ORM\Mapping as ORM;


/**
 *   @ORM\Entity
 */

class Flat extends Estate
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
     * @var integer
     *
     * @ORM\Column(name="volume", type="integer")
     */
    private $volume;



  

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
