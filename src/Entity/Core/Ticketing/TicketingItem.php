<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 7/11/2018
 * Time: 3:32 PM
 */

namespace App\Entity\Core\Ticketing;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="ticketing_item")
 * @ORM\Entity()
 */
class TicketingItem extends AbstractStoreItem {
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="date_immutable")
     */
    private $validTill;

    /**
     * @return \DateTimeImmutable
     */
    public function getValidTill(): \DateTimeImmutable {
        return $this->validTill;
    }

    /**
     * @param \DateTimeImmutable $validTill
     * @return TicketingItem
     */
    public function setValidTill(\DateTimeImmutable $validTill): TicketingItem {
        $this->validTill = $validTill;
        return $this;
    }


}