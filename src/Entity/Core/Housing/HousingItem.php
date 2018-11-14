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
     * @var string
     * @ORM\Column(type="string", length=1024)
     */
    private $location;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $propertyType;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @return string
     */
    public function getLocation(): string {
        return $this->location;
    }

    /**
     * @param string $location
     * @return HousingItem
     */
    public function setLocation(string $location): HousingItem {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string {
        return $this->propertyType;
    }

    /**
     * @param string $propertyType
     * @return HousingItem
     */
    public function setPropertyType(string $propertyType): HousingItem {
        $this->propertyType = $propertyType;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return HousingItem
     */
    public function setDuration(int $duration): HousingItem {
        $this->duration = $duration;
        return $this;
    }
}