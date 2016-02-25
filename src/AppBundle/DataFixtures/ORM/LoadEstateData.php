<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Estate;
use AppBundle\Entity\File;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class LoadEstateData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 130; $i++) {
            $estate = new Estate();
            $estate->setTitle($faker->sentence);
            $estate->setDescription($faker->sentence);
            $estate->setPrice($faker->numberBetween(10000, 500000));
            $estate->setCreatedBy('user_admin');
            $quart = rand(1,4);
            //25% $estate is for rent
            //25% $estate type is flat
            if ($quart = 4) {
                $estate->setRent(true);
            }
            $quart = rand(1,4);
            if ($quart = 4) {
                $estate->setType('flat');
                //set floor
                $countFloors = rand(4,16);
                $estate->setFloor(array('floor'=>rand(1,$countFloors), 'count_floor'=>$countFloors));
            } else {
                $typeOfEstates = array('house', 'stead', 'commerce');
                $estate->setType($typeOfEstates[$quart]);
                $estate->setType('house');
            }
            $exclusive = rand(1,10);
            if ($exclusive = 10) {
                $estate->setExclusive(true);
            }
            for ($k = 1; $k <= 5; $k++) {
                $file = new File();
                $file->setEstate($estate);
                $estate->addFile($file);
                $file->setMimeType('image/jpeg');
                $file->setName('md5(uniqid())'.'.jpg');
                $file->setSize('100000');
                $file->setPath("images/estates/foto.$k.jpg");
                $manager->persist($file);
            }
            for ($j = 1; $j <= 5; $j++) {
                $comment = new Comment();
                $comment->setEstate($estate);
                $estate->addComment($comment);
                $comment->setContent($faker->sentence);
                $comment->setCreatedBy('user_admin');
                $comment->setEnabled(false);
                $manager->persist($comment);
            }
            // set category
            $estate->setCategory($this->getReference('category1'));
            if ($estate->getType() == 'flat') {
                $cat = rand(1, 5);
                $estate->setCategory($this->getReference('category'.$cat));
            } else {
                $cat = rand(6, 14);
                $estate->setCategory($this->getReference('category'.$cat));
            }
        $manager->persist($estate);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
