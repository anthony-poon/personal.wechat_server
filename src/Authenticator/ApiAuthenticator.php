<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 8/11/2018
 * Time: 11:35 AM
 */

namespace App\Authenticator;

use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiAuthenticator extends AbstractGuardAuthenticator {
    private $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function supports(Request $request) {
        return "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
    }

    public function getCredentials(Request $request) {
        $json = json_decode($request->getContent(), true);
        return [
            "username" => $json["username"],
            "password" => $json["password"]
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        if ($credentials["username"]) {
            $user = $userProvider->loadUserByUsername($credentials["username"]);
            return $user;
        }
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user) {
        $this->encoder->isPasswordValid($user, $credentials["password"]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse([
            "status" => "failure",
            "message" => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ], Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        return new JsonResponse([
            "status" => "success",
            "message" => "Authentication successful"
        ]);
    }

    public function supportsRememberMe() {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        return new JsonResponse([
            "status" => "failure",
            "message" => "API Authentication Required"
        ], Response::HTTP_UNAUTHORIZED);
    }


}