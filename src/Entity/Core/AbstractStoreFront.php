<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 6/11/2018
 * Time: 6:03 PM
 */

namespace App\Entity\Core;

use App\Entity\Base\Asset;
use App\Entity\Base\User;
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
     * @var User
     * @ORM\ManyToOne(targetEntity="\App\Entity\Base\User", inversedBy="store")
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
     * @ORM\Column(type="datetime")
     * @ORM\Version
     * @var \DateTimeInterface
     */
    private $createTimestamp;

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
     * @return User
     */
    public function getOwner(): User {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return AbstractStoreFront
     */
    public function setOwner(User $owner): AbstractStoreFront {
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
    public function getCreateTimestamp(): \DateTimeInterface {
        return $this->createTimestamp;
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
        $rtn = [
            "id" => $this->getId(),
            "type" => $this->getType(),
            "name" => $this->getName(),
            "location" => $this->getModule()->getLocation()->getName(),
            "asset" => max($assets)
        ];
        return $rtn;
    }


}