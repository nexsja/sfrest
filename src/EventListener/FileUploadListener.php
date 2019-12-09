<?php

namespace App\EventListener;

use App\Entity\Preview;
use App\Event\FileUploaded;
use App\Exception\AttachmentExtractionException;
use App\Service\ImageExtractorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

class FileUploadListener implements EventSubscriberInterface
{
    /**
     * @var ImageExtractorService
     */
    private $imageExtractor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Finder|null
     */
    private $finder;

    public function __construct(
        ImageExtractorService $imageExtractor,
        EntityManagerInterface $entityManager,
        ?Finder $finder
    )
    {
        $this->imageExtractor = $imageExtractor;
        $this->entityManager = $entityManager;
        $this->finder = $finder ?? Finder::create();
    }

    /**
     * Extract images from uploaded document and update document attachments
     *
     * @param FileUploaded $event
     */
    public function onFileUpload(FileUploaded $event)
    {
        try {
            $filePath = $this->imageExtractor->extractImages($event->getFile());

            $document = $event->getDocument();
            $document->removePreviews();

            $files = $this->finder->in($filePath)->files()->sortByName();

            if (!$files->hasResults()) {
                return;
            }

            foreach ($files as $file) {
                /** @var \SplFileInfo $file */
                // the first file in array is also the first file in the document
                // because they are sorted by name and the extractor adds the index in which the images occur
                // in the document
                $isThumbnail = (int)filter_var($file->getBasename(), FILTER_SANITIZE_NUMBER_INT) == 0;
                $preview = new Preview($event->getDocument(), $file->getBasename(), $isThumbnail);

                $this->entityManager->persist($preview);
            }

            $this->entityManager->flush();

        } catch (AttachmentExtractionException $e) {
            // log stuff
            return;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            FileUploaded::EVENT_NAME => 'onFileUpload'
        ];
    }
}
