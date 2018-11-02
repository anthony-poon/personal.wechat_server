<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 7:59 PM
 */

namespace App\Entity\Core;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Asset
 * @package App\Entity\Base
 * @ORM\Table(name="catalog")
 * @ORM\Entity()
 */
class Catalog {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, unique=true)
     */
    private $shortString;

    /**
     * @var string
     * @ORM\Column(type="string", length=256)
     */
    private $friendlyName;


    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="CatalogItem", mappedBy="catalog")
     */
    private $catalogItems;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShortString(): string {
        return $this->shortString;
    }

    /**
     * @param string $shortString
     * @return Catalog
     */
    public function setShortString(string $shortString): Catalog {
        $this->shortString = $shortString;
        return $this;
    }

    /**
     * @return string
     */
    public function getFriendlyName(): string {
        return $this->friendlyName;
    }

    /**
     * @param string $friendlyName
     * @return Catalog
     */
    public function setFriendlyName(string $friendlyName): Catalog {
        $this->friendlyName = $friendlyName;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCatalogItems(): Collection {
        return $this->catalogItems;
    }


}