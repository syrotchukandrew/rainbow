<?php

namespace AppBundle\Utils;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\RequestStack;

class Searcher
{
    private $doctrine;

    protected $requestStack;

    /**
     * Constructor.
     *
     * @param Registry $doctrine A Registry instance
     * @param RequestStack $doctrine A RequestStack instance
     */
    public function __construct(Registry $doctrine, RequestStack $requestStack)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
    }

    public function search()
    {
        $allEstates = $this->doctrine->getRepository('AppBundle:Estate')->findAll();
        $request = $this->requestStack->getCurrentRequest();
        $slug = $request->get('slug');
        $method = $request->getMethod();
        $slugsTitles = array();
        $estates = array();
        foreach ($allEstates as $estate) {
            $estateTitle = $estate->getTitle();
            if (stristr($estateTitle, $slug)) {
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