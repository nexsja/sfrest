<?php

namespace App\Controller\ArgumentResolver;

use App\Entity\Preview;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreviewArgumentResolver implements ArgumentValueResolverInterface
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
        return $argument->getType() == Preview::class && $request->attributes->get($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        try {
            yield $this->em->getRepository(Preview::class)->find($request->attributes->get($argument->getName()));
        } catch (EntityNotFoundException | DriverException $e) {
            throw new NotFoundHttpException();
        }
    }
}
