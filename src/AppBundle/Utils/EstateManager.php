<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 14.03.16
 * Time: 23:55
 */

namespace AppBundle\Utils;

use AppBundle\Entity\Estate;

class EstateManager
{
    public function setFirstLastFloor(Estate $estate)
    {
        $floor = $estate->getFloor();
        if (($floor['floor'] == 1) || ($floor['floor'] == $floor['count_floor'])) {
            $estate->setFirstLastFloor(true);
        } else {
            $estate->setFirstLastFloor(false);
        }

    }
}
