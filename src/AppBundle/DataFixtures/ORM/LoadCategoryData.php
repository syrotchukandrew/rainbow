<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 17:14
 */

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setTitle('Дома');
        $category->setUrl('#');
        $this->setReference("houses", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Квартиры');
        $category->setUrl('#');
        $this->setReference("flats", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Участки');
        $category->setUrl('#');
        $this->setReference("steads", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Аренда ');
        $category->setUrl('#');
        $this->setReference("rent", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Комерция');
        $category->setUrl('#');
        $this->setReference("commerce", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("В городе");
        $category->setUrl("#");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category 1", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("В области");
        $category->setUrl("#");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category 2", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Комната");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category 3", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Однокомнатные");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category 4", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Двокомнатные");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category 5", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Многокомнатные");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category 6", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дачные");
        $category->setUrl("#");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category 7", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("В городе");
        $category->setUrl("#");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category 8", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("За городом");
        $category->setUrl("#");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category 9", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Аренда житла");
        $category->setUrl("#");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category 10", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Продажа");
        $category->setUrl("#");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category 11", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дома");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category 12", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Квартиры");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category 13", $category);
        $manager->persist($category);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}