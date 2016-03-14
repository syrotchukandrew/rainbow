<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 13.03.16
 * Time: 22:57
 */

namespace AppBundle\Utils;

use Doctrine\Bundle\DoctrineBundle\Registry;

class SearchManager
{

    private $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function searchEstate($data)
    {
        if ($data['price'] == 'more_then_50000') {
            $price_min = 50000;
            $price_max = null;
        } elseif ($data['price'] == 'to_50000') {
            $price_min = 20001;
            $price_max = 49999;
        } elseif ($data['price'] == 'to_20000') {
            $price_min = 0;
            $price_max = 20001;
        } else {
            $price_min = 0;
            $price_max = null;
        }
        if ($data['district'] !== null) {
            $id_district = $data['district']->getId();
        } else {
            $id_district = null;
        }
        $id_category = $data['category']->getId();
        $estates = $this->doctrine->getRepository('AppBundle:Estate')->findEstatesFromForm($id_category, $id_district, $price_min, $price_max);
        return $estates;

    }
}