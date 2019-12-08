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

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Document $document
     * @return Preview
     */
    public function setDocument(Document $document): self
    {
        $this->document = $document;
        $document->addPreview($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return Preview
     */
    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
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

    /**
     * @param bool $isThumbnail
     * @return Preview
     */
    public function setThumbnail(bool $isThumbnail): Preview
    {
        $this->isThumbnail = $isThumbnail;
        return $this;
    }
}
