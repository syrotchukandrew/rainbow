<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use App\Entity\File;


class FileManager
{
    protected $uploadableManager;
    protected $requestStack;
    protected $doctrine;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $doctrine A Registry instance
     * @param RequestStack $doctrine A RequestStack instance
     * @param UploadableManager $uploadableManager A UploadableManager instance
     */
    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack, UploadableManager $uploadableManager)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
        $this->uploadableManager = $uploadableManager;
    }

    public function fileManager($estate)
    {
        $request = $this->requestStack->getCurrentRequest();
        $entityManager = $this->doctrine->getManager();
        $files = $request->files->get('app_bundle_estate_type');
        if ($files['imageFile'][0] !== null) {
            foreach ($files['imageFile'] as $imageData) {
                $image = new File();
                $this->uploadableManager->markEntityToUpload($image, $imageData);
                $image->setEstate($estate);
                $estate->addFile($image);
                $entityManager->persist($image);
                $entityManager->flush();
            }
        }
        return ($estate);
    }
}