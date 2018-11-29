<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 10:48 AM
 */

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JsonValidator {
    private $validator;
    private $allowExtraFields = false;
    private $allowMissingFields = false;
    public function __construct(ValidatorInterface $validator){
        $this->validator = $validator;
    }

    public function validate(array $json, array $constraint): bool {
        $errors = $this->validator->validate($json, new Assert\Collection([
            "fields" => $constraint,
            "allowExtraFields" => $this->allowExtraFields,
            "allowMissingFields" => $this->allowMissingFields,
        ]));

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

    /**
     * @return bool
     */
    public function isAllowExtraFields(): bool {
        return $this->allowExtraFields;
    }

    /**
     * @param bool $allowExtraField
     * @return JsonValidator
     */
    public function setAllowExtraFields(bool $allowExtraFields): JsonValidator {
        $this->allowExtraFields = $allowExtraFields;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowMissingFields(): bool {
        return $this->allowMissingFields;
    }

    /**
     * @param bool $allowMissingFields
     * @return JsonValidator
     */
    public function setAllowMissingFields(bool $allowMissingFields): JsonValidator {
        $this->allowMissingFields = $allowMissingFields;
        return $this;
    }


}