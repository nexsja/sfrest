<?php

namespace App\Tests\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
use App\Service\UploadService;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadServiceTest extends TestCase
{
    public function testUploadFile()
    {
        //Arrange
        $dispatcher = $this->createPartialMock(EventDispatcherInterface::class, ['dispatch']);
        $service = new UploadService('some/dir', $dispatcher);
        $file = $this->createPartialMock(File::class, ['move']);
        $document = $this->createPartialMock(Document::class, ['getId']);

        //Assert
        $document->expects($this->exactly(2))->method('getId')->willReturn('unique-uuid');
        $file->expects($this->once())->method('move')->willReturn(new File('.env'));
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(FileUploaded::class),
                FileUploaded::EVENT_NAME
            );

        //Act
        $service->uploadFile($file, $document);
    }
}