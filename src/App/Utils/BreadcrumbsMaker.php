<?php

declare(strict_types=1);

namespace App\Utils;

use Huluti\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumbsMaker
{
    private $breadcrumbs;

    public function __construct(Breadcrumbs $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    public function makeBreadcrumbs($category, $estate = null)
    {
        if ($estate) {
            $this->breadcrumbs->prependItem($estate->getTitle());
        }
        $node = $category;
        while ($node) {
            $this->breadcrumbs->prependItem($node->getTitle());
            $node = $node->getParent();
        }
        $this->breadcrumbs->prependItem("site.main");

        return $this->breadcrumbs;
    }
}
