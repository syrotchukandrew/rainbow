<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Estate;
use AppBundle\Entity\Comment;
use Faker\Factory;

class LoadEstateData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 0; $i < 150; $i++) {
            $estate = new Estate();
            $estate->setTitle($faker->sentence);
            $estate->setCreatedBy('user_admin');
            $estate->setPrice(rand(10000,50000));
            if (in_array($i, array(11,22,33,44,55,66,77,88,99,111,122))) {
                $estate->setExclusive(true);
            }
            $countFloors = rand(4,16);
            $estate->setFloor(array('floor'=>rand(1,$countFloors), 'count_floor'=>$countFloors));
            $estate->setDescription($faker->realText($maxNbChars = 5000, $indexSize = 2));
            $estate->setCategory();

            $rand = rand(3, 7);
            for ($j = 0; $j < $rand; $j++) {
                $comment = new Comment();
                $comment->setCreatedBy('user_user');
                $comment->setContent($faker->realText($maxNbChars = 500, $indexSize = 2));
                $comment->setEstate($estate);
                $estate->getComments()->add($comment);
                $manager->persist($comment);
                $manager->flush();
            }
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}