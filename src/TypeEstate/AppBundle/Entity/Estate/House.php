<?php

namespace AppBundle\Entity\Estate;

use Doctrine\ORM\Mapping as ORM;

/**
 * House
 */
class House
{
    /**
     * @var float
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
