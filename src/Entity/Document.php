<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Document
{
    use EntityId;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Preview", cascade={"remove"}, mappedBy="document", orphanRemoval=true)
     */
    private $previews;

    public function __construct()
    {
        $this->previews = new ArrayCollection();
    }

    public function getPreviews()
    {
        return $this->previews;
    }

    public function removePreviews(): self
    {
        if ($this->previews->count() == 0) {
            return $this;
        }

        $this->previews = new ArrayCollection();
        return $this;
    }
}
