<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 26/11/2018
 * Time: 1:32 PM
 */

namespace App\Entity\Core;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Base\Asset;

/**
 * Class StoreItemAsset
 * @package App\Entity\Core
 * @ORM\Entity()
 * @ORM\Table("store_item_asset")
 */
class StoreItemAsset extends Asset {

    /**
     * @var AbstractStoreItem
     * @ORM\ManyToOne(targetEntity="AbstractStoreItem", inversedBy="assets")
     */
    private $storeItem;

    /**
     * @return AbstractStoreItem
     */
    public function getStoreItem(): AbstractStoreItem {
        return $this->storeItem;
    }

    /**
     * @param AbstractStoreItem $storeItem
     * @return StoreItemAsset
     */
    public function setStoreItem(AbstractStoreItem $storeItem): StoreItemAsset {
        $this->storeItem = $storeItem;
        return $this;
    }


}