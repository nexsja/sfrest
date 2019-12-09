<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Preview
{
    use EntityId;

    public const PREVIEWS_DIR = 'previews';
    public const PREVIEW_PREFIX = 'preview';

    /**
     * @var Document
     *
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="previews")
     * @ORM\JoinColumn(name="document_id")
     */
    private $document;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $image;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $isThumbnail = false;

    public function __construct(Document $document, string $image, bool $isThumbnail = false)
    {
        $this->document = $document;
        $this->image = $image;
        $this->isThumbnail = $isThumbnail;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Method used during serialization
     *
     * @return bool
     */
    public function isThumbnail(): bool
    {
        return $this->isThumbnail;
    }
}
