<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 15/11/2018
 * Time: 11:48 AM
 */

namespace App\Entity\Core;


abstract class PaddedId {
    private const PAD_LENGTH = 8;

    public function getPaddedId(): string {
        return $this->getPrefix().str_pad($this->getId(), self::PAD_LENGTH, "0", STR_PAD_LEFT);
    }

    abstract function getId(): int;

    abstract function getPrefix(): string;
}