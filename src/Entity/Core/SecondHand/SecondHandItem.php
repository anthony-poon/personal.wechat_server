<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 7/11/2018
 * Time: 3:32 PM
 */

namespace App\Entity\Core\SecondHand;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="second_hand_item")
 * @ORM\Entity()
 */
class SecondHandItem extends AbstractStoreItem {
    function getPrefix(): string {
        return $this->getStoreFront()->getModule()->getPrefix();
    }
}