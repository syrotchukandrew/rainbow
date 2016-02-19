<?php
namespace AppBundle\Entity\Estate;

use Doctrine\ORM\Mapping as ORM;

/**
*   @ORM\Entity
*/

class House extends Estate
{
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * Set price
     *
     * @param float $price
     * @return House
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }
}
