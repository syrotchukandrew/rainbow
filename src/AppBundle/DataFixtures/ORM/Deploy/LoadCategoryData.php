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
        $this->setReference("houses", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Квартиры');
        $this->setReference("flats", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Участки');
        $this->setReference("steads", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Аренда жилья');
        $this->setReference("rent", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Коммерция');
        $this->setReference("commerce", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дома в городе");
        //$category->setUrl("{{ path('show_category', {'slug': link.title}) }}");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category6", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дома за городом");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category7", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Комната");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category1", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Однокомнатные квартиры");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category2", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Двокомнатные квартиры");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category3", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Трьохкомнатные квартиры");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category4", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Многокомнатные");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category5", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дачные участки");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category8", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Участки в городе");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category9", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Участки за городом");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category10", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Аренда коммерции");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category11", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Продажа коммерции");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category12", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дома в аренду");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category13", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Квартиры в аренду");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category14", $category);
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