<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 10:48 AM
 */

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JsonValidator {
    private $validator;
    public function __construct(ValidatorInterface $validator){
        $this->validator = $validator;
    }

    public function validate(array $json, array $constraint): bool {
        $errors = $this->validator->validate($json, new Assert\Collection($constraint));

        if (0 == count($errors)) {
            return true;
        } else {
            throw new ValidationException($errors);
        }
    }

    public function validateEntity($entity): bool {
        $errors = $this->validator->validate($entity);
        if (0 == count($errors)) {
            return true;
        } else {
            throw new ValidationException($errors);
        }
    }
}