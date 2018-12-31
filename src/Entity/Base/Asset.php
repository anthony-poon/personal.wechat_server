<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 28/10/2018
 * Time: 1:15 PM
 */

namespace App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * Class Asset
 * @package App\Entity\Base
 * @ORM\Table(name="asset")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="asset_type", type="string")
 */

class Asset {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=1024)
     */
    private $imgPath;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $namespace;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var \DateTimeInterface
     */
    private $createDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImgPath(): string {
        return $this->imgPath;
    }

    /**
     * @param string $imgPath
     */
    public function setImgPath(string $imgPath): void {
        $this->imgPath = $imgPath;
    }

    /**
     * @return string
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return Asset
     */
    public function setNamespace(string $namespace): Asset
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return Asset
     */
    public function setMimeType(string $mimeType): Asset
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreateDate(): \DateTimeInterface {
        return $this->createDate;
    }

    /**
     * @param \DateTimeInterface $createDate
     * @return Asset
     */
    public function setCreateDate(\DateTimeInterface $createDate): Asset {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        if (!$this->createDate) {
            $this->createDate = new \DateTimeImmutable();
        }
    }
}