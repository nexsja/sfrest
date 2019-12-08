<?php

namespace App\Controller;

use App\Entity\Preview;
use App\Entity\Document;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(path="/documents", name="documents_")
 */
class DocumentController extends AbstractController
{
    /**
     * Arbitrary file size because why not
     * @var int
     */
    const DOCUMENT_MAX_SIZE = 5*1024*1024;

    /**
     * @Route(methods={"GET"})
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $documents = $entityManager->getRepository(Document::class)->findAll();

        return $this->json(['documents' => $documents]);
    }

    /**
     * Create the document with some possible metadata.
     * Placeholder document for future attachment upload.
     *
     * @Route(methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function postDocument(Request $request, EntityManagerInterface $entityManager) : Response
    {
        $document = new Document();
        $entityManager->persist($document);
        $entityManager->flush();

        $headers = [
            'Location' => $this->generateUrl(
                'documents_add_attachment',
                ['document' => $document->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ];
        return new Response(null, Response::HTTP_CREATED, $headers);
    }

    /**
     * This method is idempotent: only one attachment per document hence if another document is uploaded
     * the result is the same as if that would've happened the first time.
     *
     * The old attachment is overwritten and replaced.
     *
     * @Route(path="/{document}/attachment", methods={"POST"}, name="add_attachment")
     *
     * @param Document $document
     * @param Request $request
     * @param UploadService $uploadService
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function postDocumentAttachment(
        Document $document,
        Request $request,
        UploadService $uploadService,
        ValidatorInterface $validator
    ) {
        $file = $uploadService->createUploadedFile($request->getContent());

        $constraint = new File();
        $constraint->maxSize = self::DOCUMENT_MAX_SIZE;
        $constraint->mimeTypes = ['application/pdf', 'application/x-pdf'];

        $violations = $validator->validate($file, $constraint);

        if ($violations->count() > 0) {
            $return = [
                'errors' => []
            ];
            foreach ($violations as $violation) {
                /** @var $violation ConstraintViolationInterface */
                $return['errors'][$violation->getCode()] = $violation->getMessage();
            }
            return $this->json($return, Response::HTTP_BAD_REQUEST);
        }

        $uploadService->uploadFile($file, $document);

        return new Response(null, Response::HTTP_CREATED);
    }


    /**
     * @Route(path="/{document}", methods={"GET"}, name="document")
     *
     * @param Document $document
     * @return JsonResponse
     */
    public function getDocument(Document $document)
    {
        return $this->json($document);
    }

    /**
     * @Route(path="/{document}", methods={"DELETE"})
     * @param Document $document
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function deleteDocument(Document $document, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($document);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="/{document}/attachment", methods={"GET"}, name="attachment")
     * @param Document $document
     * @return BinaryFileResponse
     */
    public function getDocumentAttachment(Document $document)
    {
        $file =
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('app.document_upload_dir') . '/' .
            $document->getId() . '/' .
            $document->getId() . '.pdf'
        ;

        return new BinaryFileResponse($file);
    }

    /**
     * @Route(path="/{document}/attachment", methods={"DELETE"})
     * @param Document $document
     * @param EntityManagerInterface $entityManager
     * @param Filesystem $fs
     * @return Response
     */
    public function deleteDocumentAttachment(Document $document, EntityManagerInterface $entityManager, Filesystem $fs)
    {
        $path =
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('app.document_upload_dir') . '/' .
            $document->getId()
        ;

        $document->removePreviews();
        $fs->remove($path);

        $entityManager->persist($document);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(path="/{document}/attachment/previews", methods={"GET"}), name="attachment_previews")
     *
     * @param Document $document
     * @return JsonResponse
     */
    public function getDocumentAttachmentPreviews(Document $document)
    {
        return $this->json($document->getPreviews());
    }

    /**
     * @Route(path="/{document}/attachment/previews/{preview}", methods={"GET"}, name="attachment_preview")
     * @param Document $document
     * @param Preview $preview
     * @return BinaryFileResponse
     */
    public function getDocumentAttachmentPreview(Document $document, Preview $preview)
    {
        $file =
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('app.document_upload_dir') . '/' .
            $document->getId() . '/' .
            Preview::PREVIEWS_DIR . '/' .
            $preview->getImage()
        ;

        return new BinaryFileResponse($file);
    }
}
