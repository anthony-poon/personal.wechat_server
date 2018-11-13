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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class WeChatAuthenticator extends AbstractGuardAuthenticator {
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function supports(Request $request) {
        // Is json and have openId field
        return "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
    }

    public function getCredentials(Request $request) {
        $json = json_decode($request->getContent(), true);
        return [
            "openId" => $json["openId"]
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        if ($credentials["openId"]) {
            $user = $this->em->getRepository(User::class)->findOneBy([
                "weChatOpenId" => $credentials["openId"]
            ]);
            if (!$user) {
                $user = new User();
                $user->setUsername($credentials["openId"]);
                $user->setWeChatOpenId($credentials["openId"]);
                $user->setFullName($credentials["openId"]);
                /* @var \App\Entity\Base\SecurityGroup $userGroup */
                $userGroup = $this->em->getRepository(SecurityGroup::class)->findOneBy([
                    "siteToken" => "ROLE_USER"
                ]);
                $userGroup->getChildren()->add($user);
                $this->em->persist($user);
                $this->em->persist($userGroup);
                $this->em->flush();
            }
            return $user;
        }
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user) {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse([
            "status" => "failure",
            "message" => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ], Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        return null;
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