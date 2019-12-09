<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class AttachmentValidatorService
{
    /**
     * Arbitrary file size because why not
     * @var int
     */
    const DOCUMENT_MAX_SIZE = 5*1024*1024;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ConstraintViolationListInterface
     */
    private $violations;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param File $file
     * @return bool
     */
    public function isValid(File $file) : bool
    {
        $constraint = new FileConstraint();
        $constraint->maxSize = self::DOCUMENT_MAX_SIZE;
        $constraint->mimeTypes = ['application/pdf', 'application/x-pdf'];

        $this->violations = $this->validator->validate($file, $constraint);

        return $this->violations->count() == 0;
    }

    /**
     * Get file violations
     *
     * @return array
     */
    public function getViolations() : array
    {
        $errors = [];
        foreach ($this->violations as $violation) {
            /** @var $violation ConstraintViolationInterface */
            $errors[$violation->getCode()] = $violation->getMessage();
        }

        return $errors;
    }
}
