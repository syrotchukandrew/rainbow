<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

#[AsDoctrineListener(event: Events::preRemove, connection: 'default')]
class FileCacheRemoveEventSubscriber
{
    public function __construct(private CacheManager $cacheManager)
    {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $file = $args->getObject();
        if ($file instanceof File && $file->getName() !== null && file_exists($file->getPath())) {
            $this->cacheManager->remove($file->getPath());
        }
    }
}
