<?php

namespace spec\App\Service;

use App\Entity\Document;
use App\Event\FileUploaded;
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

    function it_should_upload_file(File $file, Document $document, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->beADoubleOf(EventDispatcherInterface::class);
        $this->beConstructedWith('some/path', $dispatcher);

        $file->beConstructedWith(['some/path', false]);
        $file->move(Argument::type('string'), Argument::type('string'))->shouldBeCalled()->willReturn($file);

        $document->beADoubleOf(Document::class);
        $document->getId()->shouldBeCalled()->willReturn('some-very-unique-uuid');

        $dispatcher->dispatch(
            Argument::type(FileUploaded::class), FileUploaded::EVENT_NAME)->shouldBeCalledTimes(1);

        $this->uploadFile($file, $document);
    }
}
