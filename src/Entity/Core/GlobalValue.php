<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 20/11/2018
 * Time: 2:43 PM
 */

namespace App\Entity\Core;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GlobalValue
 * @package App\Entity\Core
 * @ORM\Table("global_value")
 * @ORM\Entity()
 */
class GlobalValue {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, unique=true, name="g_key")
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(type="text", name="g_value", nullable=true)
     */
    private $value;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * @param string $key
     * @return GlobalValue
     */
    public function setKey(string $key): GlobalValue {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): ?string {
        return $this->value;
    }

    /**
     * @param string $value
     * @return GlobalValue
     */
    public function setValue(?string $value): GlobalValue {
        $this->value = $value;
        return $this;
    }
}