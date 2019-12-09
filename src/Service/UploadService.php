<?php

namespace App\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
use App\Exception\InvalidUploadedFileException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\File;

class UploadService
{
    /**
     * @var string
     */
    private $documentDir;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var AttachmentValidatorService
     */
    private $validatorService;

    public function __construct(
        string $documentDir,
        EventDispatcherInterface $eventDispatcher,
        AttachmentValidatorService $validatorService
    ) {
        $this->documentDir = $documentDir;
        $this->eventDispatcher = $eventDispatcher;
        $this->validatorService = $validatorService;
    }

    /**
     * We want to trigger an event when we've successfully uploaded a file
     *
     * @param string $content
     * @param Document $document
     * @throws InvalidUploadedFileException Thrown when the uploaded file is invalid
     */
    public function uploadDataToFile(string $content, Document $document)
    {
        $file = $this->createUploadedFile($content);

        if (!$this->validatorService->isValid($file)) {
            throw new InvalidUploadedFileException(
                $this->validatorService->getViolations()
            );
        }

        $fileDir = $this->documentDir . '/' . $document->getId();
        $file = $file->move($fileDir, $document->getId() . '.pdf');

        $this->eventDispatcher->dispatch(new FileUploaded($file, $document), FileUploaded::EVENT_NAME);
    }

    private function createUploadedFile($content)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, "w");
        fwrite($file, $content);
        $meta_data = stream_get_meta_data($file);
        $path = $meta_data['uri'];
        fclose($file);

        return new File($path);
    }

}
