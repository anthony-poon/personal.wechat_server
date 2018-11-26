<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 10:21 AM
 */

namespace App\Entity\Core\Housing;

use App\Entity\Core\AbstractModule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class HousingModule
 * @package App\Entity\Core\Housing
 * @ORM\Table(name="housing_module")
 * @ORM\Entity()
 */
class HousingModule extends AbstractModule {
    public function getName(): string {
        return "房屋转让";
    }
}