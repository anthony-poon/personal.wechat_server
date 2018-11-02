<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 28/10/2018
 * Time: 5:34 PM
 */

namespace App\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityAPIController extends Controller {

    /**
     * @Route("/api/security/login", name="security_api_login", methods={"POST"})
     */
    public function login() {
        $user = $this->getUser();
        return $this->json(array(
            'id' => $user->getId(),
            'fullName' => $user->getFullName(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ));
    }

}