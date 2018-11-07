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
 * Class AbstractStoreItem
 * @package App\Entity\Core
 * @ORM\Table(name="store_item")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="item_type", type="string")
 */
class AbstractStoreItem {
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
     * @var Store
     * @ORM\ManyToOne(targetEntity="\App\Entity\Core\Store", inversedBy="storeItems")
     */
    private $store;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $city;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="\App\Entity\Base\Asset", cascade={"remove"})
     * @ORM\JoinTable(name="store_item_asset_mapping",
     *     joinColumns={@ORM\JoinColumn(name="store_item_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="asset_id", referencedColumnName="id", unique=true, onDelete="cascade")}
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
     * @return string
     */
    public function getCity(): string {
        return $this->city;
    }

    /**
     * @param string $city
     * @return AbstractStoreItem
     */
    public function setCity(string $city): AbstractStoreItem {
        $this->city = $city;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAssets(): Collection {
        return $this->assets;
    }

    /**
     * @return Store
     */
    public function getStore(): Store {
        return $this->store;
    }

    /**
     * @param Store $store
     */
    public function setStore(Store $store): void {
        $this->store = $store;
    }

}