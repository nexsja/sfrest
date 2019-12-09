<?php

namespace spec\App\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
use App\Service\AttachmentValidatorService;
use App\Service\UploadService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadServiceSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UploadService::class);
    }

    function it_should_upload_file(Document $document, EventDispatcherInterface $dispatcher, AttachmentValidatorService $validatorService)
    {
        $dispatcher->beADoubleOf(EventDispatcherInterface::class);
        $validatorService->beADoubleOf(AttachmentValidatorService::class);
        $this->beConstructedWith('some/path', $dispatcher, $validatorService);

        $validatorService->isValid(Argument::type(File::class))->shouldBeCalled()->willReturn(true);

        $document->beADoubleOf(Document::class);
        $document->getId()->shouldBeCalledTimes(2)->willReturn('some-very-unique-uuid');

        $dispatcher->dispatch(
            Argument::type(FileUploaded::class), FileUploaded::EVENT_NAME)->shouldBeCalledTimes(1);

        $this->uploadDataToFile("definitely a file", $document);
    }
}
