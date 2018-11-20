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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractStoreItem
 * @package App\Entity\Core
 * @ORM\Table(name="abstract_store_item")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="store_item_type", type="string")
 */
abstract class AbstractStoreItem extends PaddedId {
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
     * @var Collection
     * @ORM\ManyToMany(targetEntity="\App\Entity\Base\Asset", cascade={"remove", "persist"})
     * @ORM\JoinTable(name="store_item_asset_mapping",
     *     joinColumns={@ORM\JoinColumn(name="store_item_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="asset_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     * )
     */
    private $assets;

    /**
     * @var AbstractStoreFront
     * @ORM\ManyToOne(targetEntity="AbstractStoreFront", inversedBy="storeItems")
     */
    private $storeFront;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $price = 0.0;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isTraded = false;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $visitorCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $visitorCountModification = 0;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isActive = 1;

    /**
     * @ORM\Column(type="datetime")
     * @ORM\Version
     * @var \DateTimeInterface
     */
    private $createTimestamp;

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
     * @return Collection
     */
    public function getAssets(): Collection {
        return $this->assets;
    }

    /**
     * @return float
     */
    public function getPrice(): float {
        return $this->price;
    }

    /**
     * @param float $price
     * @return AbstractStoreItem
     */
    public function setPrice(float $price): AbstractStoreItem {
        $this->price = $price;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTraded(): bool {
        return $this->isTraded;
    }

    /**
     * @param bool $isTraded
     * @return AbstractStoreItem
     */
    public function setIsTraded(bool $isTraded): AbstractStoreItem {
        $this->isTraded = $isTraded;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisitorCount(): int {
        return $this->visitorCount;
    }

    /**
     * @param int $visitorCount
     * @return AbstractStoreItem
     */
    public function setVisitorCount(int $visitorCount): AbstractStoreItem {
        $this->visitorCount = $visitorCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisitorCountModification(): int {
        return $this->visitorCountModification;
    }

    /**
     * @param int $visitorCountModification
     * @return AbstractStoreItem
     */
    public function setVisitorCountModification(int $visitorCountModification): AbstractStoreItem {
        $this->visitorCountModification = $visitorCountModification;
        return $this;
    }

    /**
     * @return AbstractStoreFront
     */
    public function getStoreFront(): AbstractStoreFront {
        return $this->storeFront;
    }

    public function setStoreFront(AbstractStoreFront $storeFront): AbstractStoreItem {
        $this->storeFront = $storeFront;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void {
        $this->isActive = $isActive;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreateTimestamp(): \DateTimeInterface {
        return $this->createTimestamp;
    }

    public function getType() {
        $class = get_class($this);
        preg_match('/(\w+)$/', $class, $match);
        return $match[1];
    }
}