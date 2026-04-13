<?php

declare(strict_types=1);

namespace App\Utils;

use Doctrine\Persistence\ManagerRegistry;


class FinalCategoryFinder
{
    private ManagerRegistry $doctrine;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $doctrine A Registry instance
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findFinalCategories(): array
    {
        return $this->doctrine->getManager()
            ->createQuery('
                SELECT c FROM App\Entity\Category c
                WHERE NOT EXISTS (
                    SELECT 1 FROM App\Entity\Category c2 WHERE c2.parent = c
                )
            ')
            ->getResult();
    }
}