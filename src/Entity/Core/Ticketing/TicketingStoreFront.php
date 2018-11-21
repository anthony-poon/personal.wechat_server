<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:09 AM
 */

namespace App\Entity\Core\Ticketing;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="ticketing_store_front")
 * @ORM\Entity()
 */
class TicketingStoreFront extends AbstractStoreFront {

}
