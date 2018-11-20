<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 28/10/2018
 * Time: 5:34 PM
 */

namespace App\Controller\Base;

use App\Entity\Base\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SecurityAPIController extends Controller {

    /**
     * @Route("/api/security/login", name="security_api_login", methods={"POST"})
     */
    public function login() {
        $user = $this->getUser();
        return $this->json([
            'login' => true,
            'user' => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'username' => $user->getUsername(),
                'openId' => $user->getWeChatOpenId(),
            ]
        ]);
    }

    /**
     * @Route("/api/security/login", name="security_api_get_login_status", methods={"GET"})
     */
    public function getLoginStatus() {
        $user = $this->getUser();
        if ($user instanceof User) {
            return $this->json([
                'login' => true,
                'user' => [
                    'id' => $user->getId(),
                    'fullName' => $user->getFullName(),
                    'username' => $user->getUsername(),
                    'openId' => $user->getWeChatOpenId(),
                ]
            ]);
        } else {
            return $this->json([
                "login" => false
            ]);
        }
    }
}