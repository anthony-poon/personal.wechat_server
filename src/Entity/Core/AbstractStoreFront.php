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
     * @ORM\Column(type="datetime_immutable")
     * @var \DateTimeInterface
     */
    private $createTime;

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
    public function getCreateTime(): \DateTimeInterface {
        return $this->createTime;
    }

    /**
     * @param \DateTimeInterface $createTime
     */
    public function setCreateTime(\DateTimeInterface $createTime): void {
        $this->createTime = $createTime;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getType() {
        $class = get_class($this);
        preg_match('/(\w+)$/', $class, $match);
        return $match[1];
    }

    public function jsonSerialize() {
        $assets = [];
        foreach ($this->getStoreItems() as $storeItem) {
            /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
            foreach ($storeItem->getAssets() as $asset) {
                /* @var \App\Entity\Base\Asset $asset */
                $assets[] = $asset->getId();
            }
        }
        if ($assets) {
            $asset = max($assets);
        } else {
            $asset = null;
        }
        $rtn = [
            "id" => $this->getId(),
            "type" => $this->getType(),
            "isPremium" => $this->getOwner()->isPremium(),
            "name" => $this->getName(),
            "location" => $this->getModule()->getLocation()->getName(),
            "createDate" => $this->getCreateTime()->format("Y-m-d H:i:s"),
            "asset" => $asset
        ];
        return $rtn;
    }


}