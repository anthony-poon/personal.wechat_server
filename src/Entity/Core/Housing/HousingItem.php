<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 7/11/2018
 * Time: 3:32 PM
 */

namespace App\Entity\Core\Housing;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="housing_item")
 * @ORM\Entity()
 */
class HousingItem extends AbstractStoreItem {

    /**
     * @var AbstractStoreFront
     * @ORM\ManyToOne(targetEntity="HousingStoreFront", inversedBy="storeItems")
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
}