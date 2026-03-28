<?php

namespace AppBundle\Controller;

use AppBundle\Utils\BreadcrumpsMaker;
use AppBundle\Utils\FileManager;
use AppBundle\Utils\FinalCategoryFinder;
use AppBundle\Utils\SearchManager;
use AppBundle\Utils\Searcher;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * Base controller that subscribes all custom services so $this->get() works in Symfony 5.
 */
abstract class AppController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'knp_paginator'                 => PaginatorInterface::class,
            'knp_snappy.pdf'                => Pdf::class,
            'security.authentication_utils' => AuthenticationUtils::class,
            'doctrine'                      => ManagerRegistry::class,
            'app.final_category_finder'     => FinalCategoryFinder::class,
            'app.breadcrumps_maker'         => BreadcrumpsMaker::class,
            'app.file_manager'              => FileManager::class,
            'app.search'                    => SearchManager::class,
            'app.searcher'                  => Searcher::class,
            'white_october_breadcrumbs'     => Breadcrumbs::class,
        ]);
    }

    /**
     * SF5's ControllerResolver throws if setContainer() returns null (no previous container).
     * Return the new container as fallback so the resolver treats the controller as properly wired.
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        return parent::setContainer($container) ?? $container;
    }

    protected function getDoctrine(): ManagerRegistry
    {
        return $this->get('doctrine');
    }
}
