<?php

namespace App\Tests\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
use App\Exception\InvalidUploadedFileException;
use App\Service\AttachmentValidatorService;
use App\Service\UploadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class UploadServiceTest extends TestCase
{
    /**
     * @var UploadService
     */
    private $service;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $dispatcher;

    /**
     * @var AttachmentValidatorService|MockObject
     */
    private $validator;

    public function setUp()
    {
        $this->dispatcher = $this->createPartialMock(EventDispatcherInterface::class, ['dispatch']);
        $this->validator = $this->createPartialMock(AttachmentValidatorService::class, ['isValid', 'getViolations']);

        $this->service = new UploadService('some/dir', $this->dispatcher, $this->validator);
    }

    public function testUploadFile()
    {
        //Arrange
        $document = $this->createPartialMock(Document::class, ['getId']);
        $this->validator->expects($this->once())->method('isValid')->willReturn(true);
        $this->validator->expects($this->never())->method('getViolations');


        //Assert
        $document->expects($this->exactly(2))->method('getId')->willReturn('unique-uuid');

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(FileUploaded::class),
                FileUploaded::EVENT_NAME
            );

        //Act
        $this->service->uploadDataToFile("content that's definitely a file", $document);
    }

    public function testUploadingThrowsException()
    {
        //Arrange
        $this->validator->expects($this->once())->method('isValid')->willReturn(false);
        $this->validator->expects($this->once())->method('getViolations')->willReturn(['code' => 'bad stuff']);

        //Assert
        $this->expectException(InvalidUploadedFileException::class);
        $this->dispatcher->expects($this->never())->method('dispatch');

        //Act
        $this->service->uploadDataToFile("content that's definitely a file", new Document());
    }
}