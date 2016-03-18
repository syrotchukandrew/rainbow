<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 13.03.16
 * Time: 22:57
 */

namespace AppBundle\Utils;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumpsMaker
{
    private $breadcrumbs;

    public function __construct(Breadcrumbs $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    public function makeBreadcrumps($category, $estate = null)
    {
        if ($estate) {
            $this->breadcrumbs->prependItem($estate->getTitle());
        }
        $node = $category;
        while ($node) {
            $this->breadcrumbs->prependItem($node->getTitle());
            $node = $node->getParent();
        }
        $this->breadcrumbs->prependItem("Главная");

        return $this->breadcrumbs;
    }
}