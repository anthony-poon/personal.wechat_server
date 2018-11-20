<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:10 AM
 */

namespace App\Entity\Core;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractStoreItem
 * @package App\Entity\Core
 * @ORM\Table(name="abstract_module")
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="module_type", type="string")
 */
abstract class AbstractModule extends PaddedId {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Location
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="modules")
     */
    private $location;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="AbstractStoreFront", mappedBy="module", cascade={"remove"})
     */
    private $storeFronts;

    public function __construct() {
        $this->storeFronts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location {
        return $this->location;
    }

    /**
     * @param Location $location
     * @return AbstractModule
     */
    public function setLocation(Location $location): AbstractModule {
        $this->location = $location;
        return $this;
    }

    public function getStoreFronts(): Collection {
        return $this->storeFronts;
    }

    abstract function getName(): string;
}