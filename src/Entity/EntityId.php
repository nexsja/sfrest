<?php

namespace App\Entity;

trait EntityId
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}
