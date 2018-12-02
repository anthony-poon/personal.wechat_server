<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 6/11/2018
 * Time: 6:03 PM
 */

namespace App\Entity\Core;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractStoreItem
 * @package App\Entity\Core
 * @ORM\Table(name="abstract_store_front", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UNIQUE_OWNER_MODULE", columns={"owner_id", "module_id"})
 * })
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="store_front_type", type="string")
 */
abstract class AbstractStoreFront implements \JsonSerializable {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var WeChatUser
     * @ORM\ManyToOne(targetEntity="WeChatUser", inversedBy="stores")
     */
    protected $owner;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var AbstractModule
     * @ORM\ManyToOne(targetEntity="AbstractModule", inversedBy="storeFronts")
     */
    private $module;
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="AbstractStoreItem", mappedBy="storeFront", cascade={"remove"})
     */
    private $storeItems;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isDisabled =false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isAutoTop = false;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var \DateTimeInterface
     */
    private $createDate;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastTopTime;

    public function __construct() {
        $this->storeItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return WeChatUser
     */
    public function getOwner(): WeChatUser {
        return $this->owner;
    }

    /**
     * @param WeChatUser $owner
     * @return AbstractStoreFront
     */
    public function setOwner(WeChatUser $owner): AbstractStoreFront {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AbstractStoreFront
     */
    public function setName(string $name): AbstractStoreFront {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getStoreItems(): Collection {
        return $this->storeItems;
    }

    public function getModule(): AbstractModule {
        return $this->module;
    }

    public function setModule(AbstractModule $module): AbstractStoreFront {
        $this->module = $module;
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
     */
    public function setCreateDate(\DateTimeInterface $createDate): void {
        $this->createDate = $createDate;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     * @return AbstractStoreFront
     */
    public function setIsDisabled(bool $isDisabled): AbstractStoreFront {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoTop(): bool {
        return $this->isAutoTop;
    }

    /**
     * @param bool $isAutoTop
     * @return AbstractStoreFront
     */
    public function setIsAutoTop(bool $isAutoTop): AbstractStoreFront {
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
     * @return AbstractStoreFront
     */
    public function setLastTopTime(\DateTimeImmutable $lastTopTime): AbstractStoreFront {
        $this->lastTopTime = $lastTopTime;
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
            $this->lastTopTime = new \lastTopTime();
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

    public function isActive($showDisabled = false) {
        $isActive = true;
        $isActive = $isActive && ($showDisabled || (!$showDisabled && !$this->isDisabled()));
        return $isActive;
    }

    public function jsonSerialize() {
        $assets = [];
        foreach ($this->getStoreItems() as $storeItem) {
            /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
            foreach ($storeItem->getAssets() as $asset) {
                /* @var StoreItemAsset $asset */
                $assets[] = $asset;
            }
        }
        usort($assets, function(StoreItemAsset $asset1, StoreItemAsset $asset2) {
            return -($asset1->getCreateDate() <=> $asset2->getCreateDate());
        });
        if ($assets) {
            $asset = $assets[0]->getId();
        } else {
            $asset = null;
        }
        $rtn = [
            "id" => $this->getId(),
            "type" => $this->getType(),
            "isAutoTop" => $this->isAutoTop(),
            "lastTopTime" => $this->getLastTopTime()->format("Y-m-d H:i:s"),
            "autoTopFrequency" => $this->getAutoTopFrequency(),
            "isDisabled" => $this->isDisabled(),
            "name" => $this->getName(),
            "location" => $this->getModule()->getLocation()->getName(),
            "createDate" => $this->getCreateDate()->format("Y-m-d H:i:s"),
            "asset" => $asset
        ];
        return $rtn;
    }


}