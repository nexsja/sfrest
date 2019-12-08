<?php

namespace App\Serializer;

use App\Entity\Preview;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PreviewNormalizer implements ContextAwareNormalizerInterface
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
     * @param Preview $preview
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|void|null
     *@throws ExceptionInterface
     */
    public function normalize($preview, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = ['document', 'image'];

        $data = $this->normalizer->normalize($preview, $format, $context);
        $data['links']['self'] = $this->router->generate('documents_attachment_preview', [
            'document' => $preview->getDocument()->getId(),
            'preview' => $preview->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Preview;
    }
}
