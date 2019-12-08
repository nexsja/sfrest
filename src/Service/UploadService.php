<?php

namespace App\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
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

    public function __construct(string $documentDir, EventDispatcherInterface $eventDispatcher)
    {
        $this->documentDir = $documentDir;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function createUploadedFile($base64Content)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, "w");
        fwrite($file, $base64Content);
        $meta_data = stream_get_meta_data($file);
        $path = $meta_data['uri'];
        fclose($file);

        return new File($path);
    }

    public function uploadFile(File $file, Document $document)
    {
        $fileDir = $this->documentDir . '/' . $document->getId();
        $file = $file->move($fileDir, $document->getId() . '.pdf');

        $this->eventDispatcher->dispatch(new FileUploaded($file, $document), FileUploaded::EVENT_NAME);
    }
}
