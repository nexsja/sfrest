<?php

namespace App\Serializer;

use App\Entity\Document;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DocumentNormalizer implements ContextAwareNormalizerInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;
    /**
     * @var ObjectNormalizer
     */
    private $normalizer;

    public function __construct(UrlGeneratorInterface $router, ObjectNormalizer $normalizer)
    {
        $this->router = $router;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Document $document
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|void|null
     * @throws ExceptionInterface
     */
    public function normalize($document, string $format = null, array $context = [])
    {
        $context[AbstractObjectNormalizer::CIRCULAR_REFERENCE_LIMIT] = 1;
        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = ['document'];

        $data = $this->normalizer->normalize($document, $format, $context);
        $data['links']['self'] = $this->router->generate('documents_document', [
            'document' => $document->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);


        $data['relationships']['attachment']['links']['self'] = $document->getPreviews()->count() > 0
            ? $this->router->generate(
                'documents_attachment',
                ['document' => $document->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            : null;

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Document;
    }
}
