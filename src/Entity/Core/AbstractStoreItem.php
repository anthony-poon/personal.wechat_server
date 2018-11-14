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
 * @ORM\Table(name="abstract_store_item")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="store_item_type", type="string")
 */
abstract class AbstractStoreItem {
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
     * @return AbstractStoreFront
     */
    public function getStoreFront(): AbstractStoreFront {
        return $this->storeFront;
    }

    public function setStoreFront(AbstractStoreFront $storeFront): AbstractStoreItem {
        $this->storeFront = $storeFront;
        return $this;
    }

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
}