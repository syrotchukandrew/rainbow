<?php
namespace AppBundle\EventListener;

use AppBundle\Entity\File;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class FileCacheRemoveEventSubscriber implements EventSubscriber
{
    protected $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManager $cacheManager A CacheManager instance
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function getSubscribedEvents()
    {
        return array(
            'preRemove'
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $file = $args->getEntity();
        if ($file instanceof File && $file->getName() !== null && file_exists($file->getPath())) {
            $this->cacheManager->remove($file->getPath());
        }
    }

}