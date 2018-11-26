<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:21 AM
 */

namespace App\Entity\Core\Ticketing;


use App\Entity\Core\AbstractModule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SecondHandItem
 * @package App\Entity\Core\SecondHand
 * @ORM\Table(name="ticketing_module")
 * @ORM\Entity()
 */
class TicketingModule extends AbstractModule {
    public function getName(): string {
        return "票务转让";
    }
}