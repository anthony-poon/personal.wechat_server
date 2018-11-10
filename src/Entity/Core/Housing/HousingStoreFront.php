<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:09 AM
 */

namespace App\Entity\Core\Housing;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="housing_store_front")
 * @ORM\Entity()
 */
class HousingStoreFront extends AbstractStoreFront {
    /**
     * @var HousingModule
     * @ORM\ManyToOne(targetEntity="HousingModule", inversedBy="storeFronts")
     */
    private $module;
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HousingItem", mappedBy="storeFront", cascade={"remove"})
     */
    private $storeItems;

    public function __construct() {
        $this->storeItems = new ArrayCollection();
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


}
