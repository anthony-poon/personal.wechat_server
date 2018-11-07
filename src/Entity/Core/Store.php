<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 6/11/2018
 * Time: 6:03 PM
 */

namespace App\Entity\Core;

use App\Entity\Base\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Asset
 * @package App\Entity\Base
 * @ORM\Table(name="store")
 * @ORM\Entity()
 */
class Store {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="\App\Entity\Base\User", inversedBy="store")
     */
    private $owner;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="\App\Entity\Core\AbstractStoreItem", mappedBy="store", cascade={"remove"})
     */
    private $storeItems;

    public function __construct() {
        $this->storeItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getOwner(): User {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return Store
     */
    public function setOwner(User $owner): Store {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getStoreItems(string $class = null): Collection {
        if ($class) {
            return $this->storeItems->filter(function(AbstractStoreItem $item) use ($class) {
                return get_class($item) === $class;
            });
        } else {
            return $this->storeItems;
        }

    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Store
     */
    public function setName(string $name): Store {
        $this->name = $name;
        return $this;
    }


}