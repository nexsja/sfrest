<?php

namespace App\Controller\ArgumentResolver;

use App\Entity\Document;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentArgumentResolver implements ArgumentValueResolverInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() == Document::class && $request->attributes->get($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        try {
            $document = $this->em->getRepository(Document::class)->find($request->attributes->get($argument->getName()));
            if (!$document) {
                throw new NotFoundHttpException();
            }

            yield $document;
        } catch (DriverException $e) {
            throw new NotFoundHttpException();
        }
    }
}
