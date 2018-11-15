<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 18/5/2018
 * Time: 5:09 PM
 */

namespace App\Entity\Base;

use App\Entity\Core\AbstractStoreFront;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class User
 * @ORM\Table(name="app_user", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UNIQUE_OPEN_ID", columns={"we_chat_open_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username", message="Username is taken already")
 * @UniqueEntity("email", message="Email is registered already")
 * @UniqueEntity("we_chat_open_id")
 */
class User extends DirectoryObject implements UserInterface, \Serializable {
    /**
     * @ORM\Column(type="string", length=512, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *      pattern="/^[\w_\.]+$/",
     *      message="Username contained invalid character"
     * )
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=256)
     * @Assert\NotBlank()
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=4096, nullable=true)
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(
     *     max=4096,
     *     min=5,
     *     maxMessage="Password too long",
     *     minMessage="Password too short (5 characters or more)"
     * )
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=128, unique=true, nullable=True)
     * @Assert\Email()
     */
    private $email = Null;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = True;

	/**
	 * Reference to the security group which this object is an immediate member
	 * @var Collection
	 * @ORM\ManyToMany(targetEntity="SecurityGroup", mappedBy="children")
	 */
	private $securityGroups;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="\App\Entity\Core\AbstractStoreFront", mappedBy="owner");
     */
	private $stores;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=512, nullable=true, unique=true)
     */
	private $weChatOpenId;

	public function __construct() {
	    parent::__construct();
	    $this->stores = new ArrayCollection();
	    $this->securityGroups = new ArrayCollection();
    }

    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized) {
        list($this->id, $this->username, $this->password) = unserialize($serialized, [
            "allowed_classes" => false
        ]);
    }

    /**
     * @return array
     */
    public function getRoles(): array {
    	// Cannot use this->getSecurityGroup because it only get immediate parents
    	$parents = new ArrayCollection($this->getParentsRecursive());
    	$parents = $parents->filter(function(DirectoryGroup $g) {
    		return $g instanceof SecurityGroup;
		});
    	$rtn = [];
    	foreach ($parents as $g) {
    		/* @var SecurityGroup $g */
			$rtn[] = $g->getSiteToken();
		}
        return $rtn;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt() {
        return Null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials() {
    	$this->plainPassword = "";
    }

    public function setUsername(string $username): self {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $password): self {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getIsActive(): ?bool {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword(string $plainPassword): self {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): ?string {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return User
     */
    public function setFullName(string $fullName): User {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSecurityGroups(): ?Collection {
		return $this->securityGroups;
	}

	public function setSecurityGroups($groups): User {
    	if ($groups instanceof Collection) {
			$this->securityGroups = $groups;
		} else {
    		$groups = new ArrayCollection($groups);
		}
    	return $this;
	}

	public function getFriendlyName(): string {
		return $this->fullName;
	}

	public function getFriendlyClassName(): string {
		return "User";
	}

    /**
     * @return Collection
     */
    public function getStores(): ?Collection {
        return $this->stores;
    }

    /**
     * @return mixed
     */
    public function getWeChatOpenId() {
        return $this->weChatOpenId;
    }

    /**
     * @param mixed $weChatOpenId
     * @return User
     */
    public function setWeChatOpenId($weChatOpenId) {
        $this->weChatOpenId = $weChatOpenId;
        return $this;
    }
}