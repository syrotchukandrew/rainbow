<?php

namespace AppBundle\Controller;

use AppBundle\Utils\BreadcrumpsMaker;
use AppBundle\Utils\FileManager;
use AppBundle\Utils\FinalCategoryFinder;
use AppBundle\Utils\SearchManager;
use AppBundle\Utils\Searcher;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * Base controller that subscribes all custom services so $this->get() works in Symfony 4.
 */
abstract class AppController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'knp_paginator'                => PaginatorInterface::class,
            'knp_snappy.pdf'               => Pdf::class,
            'security.authentication_utils' => AuthenticationUtils::class,
            'security.password_encoder'    => UserPasswordEncoderInterface::class,
            'app.final_category_finder'    => FinalCategoryFinder::class,
            'app.breadcrumps_maker'        => BreadcrumpsMaker::class,
            'app.file_manager'             => FileManager::class,
            'app.search'                   => SearchManager::class,
            'app.searcher'                 => Searcher::class,
            'white_october_breadcrumbs'    => Breadcrumbs::class,
        ]);
    }
}
