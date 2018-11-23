<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 23/11/2018
 * Time: 3:53 PM
 */

namespace App\Entity\Core;

use App\Entity\Base\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * Class WeChatUser
 * @package App\Entity\Core
 * @ORM\Table()
 * @ORM\Entity()
 */
class WeChatUser extends User{

    /**
     * @var bool
     * @ORM\Column(name="is_premium", type="boolean")
     */
    private $isPremium = false;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="\App\Entity\Core\AbstractStoreFront", mappedBy="owner");
     */
    private $stores;

    /**
     * @var string
     * @ORM\Column(type="string", length=512, nullable=true, unique=true, name="we_chat_open_id")
     */
    private $weChatOpenId;

    /**
     * @return bool
     */
    public function isPremium(): bool {
        return $this->isPremium;
    }

    /**
     * @param bool $isPremium
     * @return WeChatUser
     */
    public function setIsPremium(bool $isPremium): WeChatUser {
        $this->isPremium = $isPremium;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getStores(): Collection {
        return $this->stores;
    }

    /**
     * @param Collection $stores
     * @return WeChatUser
     */
    public function setStores(Collection $stores): WeChatUser {
        $this->stores = $stores;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeChatOpenId(): string {
        return $this->weChatOpenId;
    }

    /**
     * @param string $weChatOpenId
     * @return WeChatUser
     */
    public function setWeChatOpenId(string $weChatOpenId): WeChatUser {
        $this->weChatOpenId = $weChatOpenId;
        return $this;
    }
}