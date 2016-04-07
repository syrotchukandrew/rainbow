<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 16.03.16
 * Time: 23:34
 */
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MenuItem;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class LoadMenuItemData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for ($i = 1; $i <= 2; $i++) {
            $menuItem = new MenuItem();
            $menuItem->setTitle($faker->word);
            $menuItem->setDescription($faker->text());
            $manager->persist($menuItem);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}