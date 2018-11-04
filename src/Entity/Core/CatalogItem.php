<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 8:16 PM
 */

namespace App\Entity\Core;

use App\Entity\Base\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Asset
 * @package App\Entity\Base
 * @ORM\Table(name="catalog_item")
 * @ORM\Entity()
 */
class CatalogItem {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=256)
     */
    private $name;

    /**
     * @var string|void
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="\App\Entity\Base\User", inversedBy="ownedItems")
     */
    private $owner;

    /**
     * @var Catalog
     * @ORM\ManyToOne(targetEntity="\App\Entity\Core\Catalog", inversedBy="catalogItems")
     */
    private $catalog;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $region;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="\App\Entity\Base\Asset")
     * @ORM\JoinTable(name="catalog_item_asset_mapping",
     *     joinColumns={@ORM\JoinColumn(name="catalog_item", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="catalog_id", referencedColumnName="id")}
     * )
     */
    private $assets;

    public function __construct() {
        $this->assets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @param string|void $description
     */
    public function setDescription($description): void {
        $this->description = $description;
    }

    /**
     * @return User
     */
    public function getOwner(): User {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void {
        $this->owner = $owner;
    }

    /**
     * @return Catalog
     */
    public function getCatalog(): Catalog {
        return $this->catalog;
    }

    /**
     * @param Catalog $catalog
     */
    public function setCatalog(Catalog $catalog): void {
        $this->catalog = $catalog;
    }

    /**
     * @return string
     */
    public function getRegion(): string {
        return $this->region;
    }

    /**
     * @param string $region
     * @return CatalogItem
     */
    public function setRegion(string $region): CatalogItem {
        $this->region = $region;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAssets(): Collection {
        return $this->assets;
    }
}