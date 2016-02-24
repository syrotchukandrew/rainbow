<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 24.02.16
 * Time: 13:39
 */
namespace AppBundle\DataFixtures;

use AppBundle\Entity\District;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDistrictData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $districts = array('Центр', 'Казбет', 'Днепровский', 'Хим. посолек', 'ЮЗР', 'Громова', 'Луна', 'Сосновка', 'Дахновка', 'Школьная');
        for ($i = 0; $i < count($districts); $i++) {
            $district = new District();
            $district->setTitle($districts[$i]);
            $manager->persist($district);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}