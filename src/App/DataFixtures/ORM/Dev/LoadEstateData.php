<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM\Dev;

use App\Entity\Comment;
use App\Entity\Estate;
use App\Entity\File;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadEstateData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i <= 200; $i++) {
            $estate = new Estate();
            $estate->setTitle($faker->sentence);
            $estate->setDescription($faker->sentence);
            $estate->setPrice($faker->numberBetween(10000, 500000));
            $estate->setCreatedBy('user_manager' . rand(0, 2));
            $estate->setDistrict($this->getReference('district' . rand(1, 10), \App\Entity\District::class));

            $exclusive = rand(1, 10);
            if ($exclusive == 10) {
                $estate->setExclusive(true);
            }
            for ($k = 1; $k <= 5; $k++) {
                $file = new File();
                $file->setEstate($estate);
                $estate->addFile($file);
                $file->setMimeType('image/jpeg');
                $file->setName(md5(uniqid('sdfadf')) . '.jpg');
                $file->setSize('100000');
                $file->setPath("images/estates/foto" . rand(1, 9) . ".jpg");
                $manager->persist($file);
            }
            for ($j = 1; $j <= 5; $j++) {
                $comment = new Comment();
                $comment->setEstate($estate);
                $estate->addComment($comment);
                $comment->setContent($faker->sentence);
                $enable = rand(1,2);
                if ($enable === 1) {
                    $comment->setEnabled(false);
                } else {
                    $comment->setEnabled(true);
                }
                $comment->setCreatedBy('user_user1');

                $manager->persist($comment);
            }
            // flats - 40%
            // set category
            // for rent - 20%
            $quart = rand(0, 9);
            if ($quart <= 3) {
                $cat = rand(1, 5);
                $estate->setCategory($this->getReference('category' . $cat, \App\Entity\Category::class));
                $countFloors = rand(4, 16);
                $estate->setFloor(array('floor' => rand(1, $countFloors), 'count_floor' => $countFloors));
                $floor = $estate->getFloor();
                if (($floor['floor'] == 1) || ($floor['floor'] == $floor['count_floor'])) {
                    $estate->setFirstLastFloor(true);
                } else {
                    $estate->setFirstLastFloor(false);
                }
            } elseif ($quart == 4 || $quart == 5) {
                $cat = rand(13, 14);
                $estate->setCategory($this->getReference('category' . $cat, \App\Entity\Category::class));
            } else {
                $cat = rand(6, 12);
                $estate->setCategory($this->getReference('category' . $cat, \App\Entity\Category::class));
            }

            $manager->persist($estate);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}
