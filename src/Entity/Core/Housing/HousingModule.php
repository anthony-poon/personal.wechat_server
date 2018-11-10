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

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HousingStoreFront", mappedBy="module")
     */
    private $storeFronts;

    public function __construct() {
        $this->storeFronts = new ArrayCollection();
    }

    public function getName(): string {
        return "房屋交易";
    }

    public function getStoreFronts(): Collection {
        return $this->storeFronts;
    }
}