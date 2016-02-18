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

class LoadCategoryData extends  AbstractFixture implements  OrderedFixtureInterface, ContainerAwareInterface
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
        $category->setName('root');
        $category->setParent(null);
        $category->setUrl('#');
        $this->setReference("category parent", $category);
        $manager->persist($category);

        for ($i=1; $i<=3; $i++) {
            $category = new Category();
            $category->setName("child".$i);
            $category->setParent($this->getReference("category parent"));
            $category->setUrl('#');
            $this->setReference("category child".$i, $category);
            $manager->persist($category);
        }

        for ($j=1; $j<=3; $j++) {
            for ($k=1; $k<=2; $k++) {
                $category = new Category();
                $category->setName("child".$j.'.'.$k);
                $category->setParent($this->getReference("category child".$j));
                $category->setUrl('#');
                $this->setReference("category child".$j.'.'.$k, $category);
                $manager->persist($category);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }

}