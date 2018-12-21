<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 8:16 PM
 */

namespace App\Entity\Core;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractStoreItem
 * @package App\Entity\Core
 * @ORM\Table(name="abstract_store_item")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="store_item_type", type="string")
 */
abstract class AbstractStoreItem implements \JsonSerializable {
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
     * @ORM\OneToMany(targetEntity="StoreItemAsset", cascade={"remove"}, mappedBy="storeItem")
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
    private $isDisabled = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isAutoTop = false;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var \DateTimeImmutable
     */
    private $createDate;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $weChatId;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastTopTime;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $currency = "GBP";

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
    public function isDisabled(): bool {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     */
    public function setIsDisabled(bool $isDisabled): void {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate(): \DateTimeImmutable {
        return $this->createDate;
    }

    /**
     * @param \DateTimeImmutable $createDate
     * @return AbstractStoreItem
     */
    public function setCreateDate(\DateTimeImmutable $createDate): AbstractStoreItem {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeChatId(): ?string {
        return $this->weChatId;
    }

    /**
     * @param string $weChatId
     * @return AbstractStoreItem
     */
    public function setWeChatId(string $weChatId = null): AbstractStoreItem {
        $this->weChatId = $weChatId;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        if (!$this->createDate) {
            $this->createDate = new \DateTimeImmutable();
        }
        if (!$this->lastTopTime) {
            $this->lastTopTime = new \DateTimeImmutable();
        }
    }

    public function getAutoTopFrequency() {
        return 24;
    }

    public function getType() {
        $class = get_class($this);
        preg_match('/(\w+)$/', $class, $match);
        return $match[1];
    }

    public function isActive($showTraded = false, $showDisabled = false, $showExpired = false) {
        $isActive = true;
        $isActive = $isActive && ($showTraded || (!$showTraded && !$this->isTraded()));
        $isActive = $isActive && ($showDisabled || (!$showDisabled && !$this->isDisabled()));
        $isActive = $isActive && ($showExpired || (!$showExpired && !$this->isExpired()));
        return $isActive;
    }

    /**
     * @return bool
     */
    public function isAutoTop(): bool {
        return $this->isAutoTop;
    }

    /**
     * @param bool $isAutoTop
     * @return AbstractStoreItem
     */
    public function setIsAutoTop(bool $isAutoTop): AbstractStoreItem {
        $this->isAutoTop = $isAutoTop;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastTopTime(): \DateTimeImmutable {
        return $this->lastTopTime;
    }

    /**
     * @param \DateTimeImmutable $lastTopTime
     * @return AbstractStoreItem
     */
    public function setLastTopTime(\DateTimeImmutable $lastTopTime): AbstractStoreItem {
        $this->lastTopTime = $lastTopTime;
        return $this;
    }

    public function getExpireDate(): \DateTimeImmutable {
        return $this->getCreateDate()->modify("+7 day");
    }

    public function isExpired(): bool {
        $now = new \DateTimeImmutable();
        return $now > $this->getExpireDate();
    }

    /**
     * @return string
     */
    public function getCurrency(): string {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return AbstractStoreItem
     */
    public function setCurrency(string $currency): AbstractStoreItem {
        $this->currency = $currency;
        return $this;
    }



    public function jsonSerialize() {
        $rtn = [
            "id" => $this->getId(),
            "type" => $this->getType(),
            "isAutoTop" => $this->isAutoTop(),
            "autoTopFrequency" => $this->getAutoTopFrequency(),
            "lastTopTime" => $this->getLastTopTime()->format("Y-m-d H:i:s"),
            "location" => $this->getStoreFront()->getModule()->getLocation()->getName(),
            "name" => $this->getName(),
            "openId" => $this->getStoreFront()->getOwner()->getWeChatOpenId(),
            "description" => $this->getDescription(),
            "price" => $this->getPrice(),
            "currency" => $this->getCurrency(),
            "weChatId" => $this->getWeChatId(),
            "visitorCount" => $this->getVisitorCount() + $this->getVisitorCountModification(),
            "createDate" => $this->getCreateDate()->format("Y-m-d H:i:s"),
            "expireDate" => $this->getExpireDate()->format("Y-m-d H:i:s"),
            "isDisabled" => $this->isDisabled(),
            "isExpired" => $this->isExpired(),
            "isTraded" => $this->isTraded(),
            "assets" => array_reverse($this->getAssets()->map(function(StoreItemAsset $asset){
                return $asset->getId();
            })->toArray())
        ];
        return $rtn;
    }
}