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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setTitle('Houses');
        $category->setUrl('#');
        $this->setReference("houses", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Flats');
        $category->setUrl('#');
        $this->setReference("flats", $category);
        $manager->persist($category);

        $category = new Category();
        $category->setTitle('Steads');
        $category->setUrl('#');
        $this->setReference("steads", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Rent");
        $category->setUrl("#");
        $category->setParent($this->getReference("houses"));
        $this->setReference("rent_for_houses", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Houses in town");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_houses"));
        $this->setReference("rent_for_houses_in_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Houses outside the town");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_houses"));
        $this->setReference("rent_for_houses_outside_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Buy");
        $category->setUrl("#");
        $category->setParent($this->getReference("houses"));
        $this->setReference("buy_for_houses", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Houses in town");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_houses"));
        $this->setReference("buy_for_houses_in_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Houses outside the town");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_houses"));
        $this->setReference("buy_for_houses_outside_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Rent");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("rent_for_flats", $category);
        $manager->persist($category);


        $category =new Category();
        $category->setTitle("Studio apartment");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_flats"));
        $this->setReference("rent_for_flats_studio_apartment", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("two-roomed flat");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_flats"));
        $this->setReference("rent_for_flats_two_roomed_flat", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("three-roomed and upper flat");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_flats"));
        $this->setReference("rent_for_flats_tree_roomed_flat", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Buy");
        $category->setUrl("#");
        $category->setParent($this->getReference("flats"));
        $this->setReference("buy_for_flats", $category);
        $manager->persist($category);


        $category =new Category();
        $category->setTitle("Studio apartment");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_flats"));
        $this->setReference("buy_for_flats_studio_apartment", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("two-roomed flat");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_flats"));
        $this->setReference("buy_for_flats_two_roomed_flat", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("three-roomed and upper flat");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_flats"));
        $this->setReference("buy_for_flats_tree_roomed_flat", $category);
        $manager->persist($category);


        $category =new Category();
        $category->setTitle("Rent");
        $category->setUrl("#");
        $category->setParent($this->getReference("steads"));
        $this->setReference("rent_for_steads", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Steads in town");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_steads"));
        $this->setReference("rent_for_steads_in_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Steads outside the town");
        $category->setUrl("#");
        $category->setParent($this->getReference("rent_for_steads"));
        $this->setReference("rent_for_steads_outside_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Buy");
        $category->setUrl("#");
        $category->setParent($this->getReference("steads"));
        $this->setReference("buy_for_steads", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Steads in town");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_steads"));
        $this->setReference("buy_for_steads_in_town", $category);
        $manager->persist($category);

        $category =new Category();
        $category->setTitle("Steads outside the town");
        $category->setUrl("#");
        $category->setParent($this->getReference("buy_for_steads"));
        $this->setReference("buy_for_steads_in_town", $category);
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