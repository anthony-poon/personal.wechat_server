<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 22/11/2018
 * Time: 3:34 PM
 */

namespace App\Voter;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\WeChatUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StoreFrontVoter extends Voter {
    const CREATE = "create";
    const READ = "read";
    const UPDATE = "update";
    const DELETE = "delete";

    protected function supports($attribute, $subject) {
        if ($subject instanceof AbstractStoreFront) {
            return true;
        }
        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        /* @var \App\Entity\Core\AbstractStoreFront $subject */
        $user = $token->getUser();
        if (!$user instanceof WeChatUser) {
            return false;
        }
        $isAdmin = in_array("ROLE_ADMIN", $user->getRoles());
        switch ($attribute) {
            case self::CREATE:
                return true;
            case self::READ:
                return true;
            case self::UPDATE:
                return $isAdmin || $subject->getOwner() === $user;
            case self::DELETE:
                return $isAdmin || $subject->getOwner() === $user;
        }
        return false;
    }

}