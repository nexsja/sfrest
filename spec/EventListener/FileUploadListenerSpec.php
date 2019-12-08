<?php

namespace spec\App\EventListener;

use App\Entity\Document;
use App\Entity\Preview;
use App\Event\FileUploaded;
use App\EventListener\FileUploadListener;
use App\Service\ImageExtractorService;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class FileUploadListenerSpec extends ObjectBehavior
{
    function let(
        ImageExtractorService $imageExtractor,
        EntityManagerInterface $entityManager,
        Finder $finder
    ) {
        $imageExtractor->beADoubleOf(ImageExtractorService::class);
        $entityManager->beADoubleOf(EntityManagerInterface::class);
        $finder->beADoubleOf(Finder::class);

        $this->beConstructedWith($imageExtractor, $entityManager, $finder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileUploadListener::class);
    }

    function it_doesnt_do_anything_if_no_images_were_extracted(
        ImageExtractorService $imageExtractor,
        EntityManagerInterface $entityManager,
        Finder $finder
    ) {
        $file = new File('.env');
        $document = new Document();

        $imageExtractor->extractImages($file)->shouldBeCalled()->willReturn('some_path');
        $finder->in(Argument::is('some_path'))->shouldBeCalled()->willReturn($finder);
        $finder->files()->shouldBeCalled()->willReturn($finder);
        $finder->sortByName()->shouldBeCalled()->willReturn($finder);
        $finder->hasResults()->shouldBeCalled()->willReturn(false);
        $finder->getIterator()->shouldNotBeCalled();

        $entityManager->persist(Argument::any())->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $event = new FileUploaded($file, $document);
        $this->onFileUpload($event);
    }

    public function it_attaches_extracted_images_to_document(
        ImageExtractorService $imageExtractor,
        EntityManagerInterface $entityManager,
        Finder $finder
    ) {
        $file = new File('.env');
        $document = new Document();

        $imageExtractor->extractImages($file)->shouldBeCalled()->willReturn('some_path');
        $finder->in(Argument::is('some_path'))->shouldBeCalled()->willReturn($finder);
        $finder->files()->shouldBeCalled()->willReturn($finder);
        $finder->sortByName()->shouldBeCalled()->willReturn($finder);
        $finder->hasResults()->shouldBeCalled()->willReturn(true);
        $finder->getIterator()->shouldBeCalled()->will(function () {
            yield new \SplFileInfo('file_name');
        });

        $entityManager->persist(Argument::type(Preview::class))->shouldBeCalledOnce();
        $entityManager->flush()->shouldBeCalledOnce();

        $event = new FileUploaded($file, $document);
        $this->onFileUpload($event);
    }
}
