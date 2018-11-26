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
    private $isSticky = false;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var \DateTimeInterface
     */
    private $createDate;

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
    public function isSticky(): bool {
        return $this->isSticky;
    }

    /**
     * @param bool $isSticky
     * @return AbstractStoreFront
     */
    public function setIsSticky(bool $isSticky): AbstractStoreFront {
        $this->isSticky = $isSticky;
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

    public function getType() {
        $class = get_class($this);
        preg_match('/(\w+)$/', $class, $match);
        return $match[1];
    }

    public function isActive() {
        return $this->getOwner()->getIsActive();
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
        $asset = $assets[0]->getId();
        $rtn = [
            "id" => $this->getId(),
            "type" => $this->getType(),
            "isActive" => $this->isActive(),
            "isSticky" => $this->isSticky(),
            "isPremium" => $this->getOwner()->isPremium(),
            "name" => $this->getName(),
            "location" => $this->getModule()->getLocation()->getName(),
            "createDate" => $this->getCreateDate()->format("Y-m-d H:i:s"),
            "asset" => $asset
        ];
        return $rtn;
    }


}