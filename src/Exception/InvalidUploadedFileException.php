<?php

namespace App\Exception;

use Throwable;

class InvalidUploadedFileException extends \Exception
{
    /**
     * @var array
     */
    private $errors = [];

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct(implode("\n", $errors));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
