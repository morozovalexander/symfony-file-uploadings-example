<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use AppBundle\Entity\Product;
use AppBundle\Service\FileUploader;

class ImageUploadListener
{
    private $uploader;

    /**
     * ImageUploadListener constructor.
     * @param FileUploader $uploader
     */
    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    /**
     * Remove file from server
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Product) {
            return;
        }

        /** @var File $imageFile */
        $imageFile = $entity->getImage();

        if ($imageFile) {
            $this->uploader->removeUpload($imageFile->getFilename());
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        // 'image' not changed
        if (!$args->hasChangedField('image')){
            return;
        }

        $oldImage = $args->getOldValue('image');

        if (is_null($args->getNewValue('image'))) {
            // don't overwrite if no file submitted
            $entity->setImage($oldImage);
        } else {
            // remove and upload new file
            $this->uploader->removeUpload($oldImage);
            $this->uploadFile($entity);
        }
    }

    /**
     * Upload image file
     *
     * @param $entity
     */
    private function uploadFile($entity)
    {
        if (!method_exists($entity, 'getImage') || !method_exists($entity, 'setImage')) {
            return; //todo: probably can add an interface for image holder entities
        }

        // upload only works for Product entities
        if (!$entity instanceof Product) {
            return;
        }

        $file = $entity->getImage();

        // only upload new files
        if (!$file instanceof UploadedFile) {
            return;
        }

        $fileName = $this->uploader->upload($file);
        $entity->setImage($fileName);
    }

    /**
     * Set File instead string to image field
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Product) {
            return;
        }

        if ($fileName = $entity->getImage()) {
            $entity->setImage(new File($this->uploader->getTargetDir() . '/' . $fileName));
        }
    }
}