<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 13/11/2018
 * Time: 11:03 AM
 */

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ValidationException extends \Exception {
    private $errors = [];
    public function __construct(ConstraintViolationListInterface $errors, int $code = 0, Throwable $previous = null) {
        foreach ($errors as $error) {
            /* @var $error \Symfony\Component\Validator\ConstraintViolation */
            $this->errors[$error->getPropertyPath()] = $error->getMessage();
        }
        parent::__construct("The input contain invalid values.", $code, $previous);
    }

    public function getErrors() {
        return $this->errors;
    }
}