<?php

namespace App\Event;

use App\Entity\Document;
use Symfony\Component\HttpFoundation\File\File;

class FileUploaded
{
    const EVENT_NAME = 'app.file_uploaded';

    /**
     * @var File
     */
    private $uploadedFile;

    /**
     * @var Document
     */
    private $document;

    public function __construct(File $uploadedFile, Document $document)
    {
        $this->uploadedFile = $uploadedFile;
        $this->document = $document;
    }

    public function getFile() : File
    {
        return $this->uploadedFile;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }
}
