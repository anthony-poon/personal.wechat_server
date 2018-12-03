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
     * @ORM\Column(type="string", nullable=true)
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
     * @return string
     */
    public function getDuration(): ?string {
        return $this->duration;
    }

    /**
     * @param string $duration
     * @return HousingItem
     */
    public function setDuration(?string $duration): HousingItem {
        $this->duration = $duration;
        return $this;
    }

    public function getExpireDate(): \DateTimeImmutable {
        return $this->getCreateDate()->modify("+14 day");
    }

    public function jsonSerialize() {
        $rtn = parent::jsonSerialize();
        $rtn["location"] = $this->getLocation();
        $rtn["propertyType"] = $this->getPropertyType();
        $rtn["duration"] = $this->getDuration();
        return $rtn;
    }
}