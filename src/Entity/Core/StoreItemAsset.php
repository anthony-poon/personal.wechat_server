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
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $thumbnailBase64;

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

    /**
     * @return null|string
     */
    public function getThumbnailBase64(): ?string {
        if ($this->thumbnailBase64) {
            return $this->thumbnailBase64;
        } else {
            return $this->getBase64();
        }

    }

    /**
     * @param null|string $thumbnailBase64
     * @return StoreItemAsset
     */
    public function setThumbnailBase64(?string $thumbnailBase64): StoreItemAsset {
        $this->thumbnailBase64 = $thumbnailBase64;
        return $this;
    }
}