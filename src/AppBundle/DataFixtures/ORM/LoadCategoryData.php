<?php
/*

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Category;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $titles = ['sport', 'nature', 'fishing', 'finance', 'religion', 'hobby',
            'gossip', 'technology', 'money', 'resorts'];
        //$faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $tag = new Tag();
            $tag->setTitle($titles[$i]);
            $manager->persist($tag);
            $manager->flush();

            $arrayId = null;
            $arrayId = array();
            while (count($arrayId) < rand(5,13)) {
                $id = rand(1, 50);
                if ((array_search($id, $arrayId)) === false) {
                    $arrayId[] = $id;
                    $postFromBase = $this->getReference("$id");
                    $postFromBase->getTags()->add($tag);
                    $tag->getPosts()->add($postFromBase);
                }
            }
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 2;
    }
}*/