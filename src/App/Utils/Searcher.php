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

    public function search(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug = $request->query->get('slug', '');

        if ($slug === '') {
            return [];
        }

        $estates = $this->doctrine->getRepository(\App\Entity\Estate::class)
            ->findByTitleSearch($slug);

        if ($request->getMethod() === 'GET') {
            $slugsTitles = [];
            foreach ($estates as $estate) {
                $slugsTitles[$estate->getSlug()] = $estate->getTitle();
            }
            return $slugsTitles;
        }

        return $estates;
    }

}