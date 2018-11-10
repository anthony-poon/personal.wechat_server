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
abstract class AbstractModule{
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

    /**
     * @return Collection
     */
    abstract public function getStoreFronts(): Collection;

    abstract public function getName(): string;
}