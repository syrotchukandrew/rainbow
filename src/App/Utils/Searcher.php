<?php

declare(strict_types=1);

namespace App\Utils;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

class Searcher
{
    private $doctrine;

    protected $requestStack;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $doctrine A Registry instance
     * @param RequestStack $doctrine A RequestStack instance
     */
    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
    }

    public function search()
    {
        $allEstates = $this->doctrine->getRepository(\App\Entity\Estate::class)->findAll();
        $request = $this->requestStack->getCurrentRequest();
        $slug = $request->query->get('slug', '');
        $method = $request->getMethod();
        $slugsTitles = array();
        $estates = array();
        foreach ($allEstates as $estate) {
            $estateTitle = $estate->getTitle();
            if ($slug !== '' && stristr($estateTitle, $slug)) {
                $estateSlug = $estate->getSlug();
                $slugsTitles[$estateSlug] = $estateTitle;
                $estates[] = $estate;
            }
        }
        if ($method == 'GET') {
            return ($slugsTitles);
        } else {
            return ($estates);
        }
    }

}