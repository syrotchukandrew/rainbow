<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 24.02.16
 * Time: 13:39
 */

declare(strict_types=1);

namespace App\DataFixtures\ORM\Deploy;

use App\Entity\District;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDistrictData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $districts = array('Центр', 'Казбет', 'Дніпровський', 'Хім. селище', 'ЮЗР', 'Громова', 'Луна', 'Соснівка', 'Дахнівка', 'Шкільна');
        for ($i = 0; $i < count($districts); $i++) {
            $district = new District();
            $district->setTitle($districts[$i]);
            $manager->persist($district);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}