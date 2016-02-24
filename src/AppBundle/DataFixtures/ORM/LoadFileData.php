<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 24.02.16
 * Time: 23:49
 */
namespace AppBundle\DataFixtures;

use AppBundle\Entity\File;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadFileData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i < 5; $i++) {
            for ($j=0; $j < 9; $j++) {
                $file = new File();
                $file->setName('md5(uniqid())'.'.jpg');
                $file->setMimeType('hz');
                $file->setSize('1000000');
                $file->setPath("images/estates/foto.$i.jpg");
                $manager->persist($file);
                $manager->flush();
            }
        }
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 4;
    }
}