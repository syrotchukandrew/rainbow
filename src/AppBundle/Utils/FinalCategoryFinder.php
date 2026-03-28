<?php

namespace AppBundle\Utils;

use Doctrine\Persistence\ManagerRegistry;


class FinalCategoryFinder
{
    protected $doctrine;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $doctrine A Registry instance
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findFinalCategories()
    {
        $finalCategories = array();
        $entityManager = $this->doctrine->getManager();
        $categories = $entityManager->getRepository(\AppBundle\Entity\Category::class)->findAll();
        foreach ($categories as $category1) {
            $flag = false;
            foreach ($categories as $category2) {
                if ($category1 === $category2->getParent()) {
                    $flag = true;
                }
            }
            if ($flag === false) {
                $finalCategories[] = $category1;
            }
        }

        return ($finalCategories);
    }
}