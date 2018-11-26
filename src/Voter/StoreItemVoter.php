<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 22/11/2018
 * Time: 3:34 PM
 */

namespace App\Voter;

use App\Entity\Base\User;
use App\Entity\Core\AbstractStoreItem;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StoreItemVoter extends Voter {
    const CREATE = "create";
    const READ = "read";
    const UPDATE = "update";
    const DELETE = "delete";

    protected function supports($attribute, $subject) {
        if ($subject instanceof AbstractStoreItem) {
            return true;
        }
        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        /* @var \App\Entity\Core\AbstractStoreItem $subject */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        $isAdmin = in_array("ROLE_ADMIN", $user->getRoles());
        switch ($attribute) {
            case self::CREATE:
                return true;
            case self::READ:
                return true;
            case self::UPDATE:
                return $isAdmin || $subject->getStoreFront()->getOwner() === $user;
            case self::DELETE:
                return $isAdmin || $subject->getStoreFront()->getOwner() === $user;
        }
        return false;
    }

}