<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 2/12/2018
 * Time: 12:56 PM
 */

namespace App\Entity\Core;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class StickyTicketController
 * @package App\Entity\Core
 * @ORM\Table()
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class StickyTicket {
    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isConsumed = false;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $createDate;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $expireDate;

    /**
     * @var WeChatUser
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\WeChatUser", inversedBy="tickets")
     */
    private $user;

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isConsumed(): bool {
        return $this->isConsumed;
    }

    /**
     * @param bool $isConsumed
     * @return StickyTicket
     */
    public function setIsConsumed(bool $isConsumed): StickyTicket {
        $this->isConsumed = $isConsumed;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreateDate(): \DateTimeImmutable {
        return $this->createDate;
    }

    /**
     * @param \DateTimeImmutable $createDate
     */
    public function setCreateDate(\DateTimeImmutable $createDate): void {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpireDate(): \DateTimeImmutable {
        return $this->expireDate;
    }

    /**
     * @param \DateTimeImmutable $expireDate
     */
    public function setExpireDate(\DateTimeImmutable $expireDate): void {
        $this->expireDate = $expireDate;
    }

    /**
     * @return WeChatUser
     */
    public function getUser(): ?WeChatUser {
        return $this->user;
    }

    /**
     * @param WeChatUser $user
     * @return StickyTicket
     */
    public function setUser(?WeChatUser $user): StickyTicket {
        $this->user = $user;
        return $this;
    }

    /**
    * @ORM\PrePersist
    */
    public function onPrePersist() {
        if (!$this->createDate) {
            $this->createDate = new \DateTimeImmutable();
        }
        if (!$this->expireDate) {
            $this->expireDate = $this->createDate->modify("+1 month");
        }
        if (!$this->code) {
            $this->code = substr(md5(random_bytes(32)),0,8);
        }
    }
}