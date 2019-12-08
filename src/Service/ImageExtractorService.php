<?php

namespace App\Service;

use App\Entity\Preview;
use App\Exception\AttachmentExtractionException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ImageExtractorService
{
    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * On success returns the path where files were extracted
     *
     * @param File $file
     * @throws AttachmentExtractionException
     * @return string
     */
    public function extractImages(File $file) : string
    {
        $previewPath = $file->getPath() . '/' . Preview::PREVIEWS_DIR;

        // Overwrite on new document upload
        if ($this->fs->exists($previewPath)) {
            $this->fs->remove($previewPath);
        }

        $this->fs->mkdir($previewPath);
        $command = 'pdfimages ' . $file->getPathname() . ' -j ' . $file->getPath() . '/' . Preview::PREVIEWS_DIR . '/' . Preview::PREVIEW_PREFIX;

        if (false === system($command)) {
            throw new AttachmentExtractionException('Could not extract images from pdf');
        }

        return $previewPath;
    }
}
