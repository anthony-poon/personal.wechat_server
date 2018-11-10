<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:21 AM
 */

namespace App\Entity\Core\SecondHand;


use App\Entity\Core\AbstractModule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="second_hand_module")
 * @ORM\Entity()
 */
class SecondHandModule extends AbstractModule {

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="SecondHandStoreFront", mappedBy="module")
     */
    private $storeFronts;

    public function __construct() {
        $this->storeFronts = new ArrayCollection();
    }

    public function getName(): string {
        return "二手交易";
    }

    public function getStoreFronts(): Collection {
        return $this->storeFronts;
    }
}