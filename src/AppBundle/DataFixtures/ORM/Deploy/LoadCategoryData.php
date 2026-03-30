<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 17:14
 */

declare(strict_types=1);

namespace AppBundle\DataFixtures\ORM\Deploy;

use AppBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $category = new Category();
        $category->setTitle('Будинки');
        $this->setReference("houses", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Квартири');
        $this->setReference("flats", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Ділянки');
        $this->setReference("steads", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Оренда житла');
        $this->setReference("rent", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Комерція');
        $this->setReference("commerce", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Будинки в місті");
        //$category->setUrl("{{ path('show_category', {'slug': link.title}) }}");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category6", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Будинки за містом");
        $category->setParent($this->getReference("houses"));
        $this->setReference("category7", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Кімната");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category1", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Однокімнатні квартири");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category2", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Двокімнатні квартири");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category3", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Трикімнатні квартири");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category4", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Багатокімнатні");
        $category->setParent($this->getReference("flats"));
        $this->setReference("category5", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Дачні ділянки");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category8", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Ділянки в місті");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category9", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Ділянки за містом");
        $category->setParent($this->getReference("steads"));
        $this->setReference("category10", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Оренда комерції");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category11", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Продаж комерції");
        $category->setParent($this->getReference("commerce"));
        $this->setReference("category12", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Будинки в оренду");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category13", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle("Квартири в оренду");
        $category->setParent($this->getReference("rent"));
        $this->setReference("category14", $category);
        $manager->persist($category);

        $manager->flush();
    }

    public function getOrder(): int
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}