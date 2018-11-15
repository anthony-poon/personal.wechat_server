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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use \GuzzleHttp\Client;

class WeChatAuthenticator extends AbstractGuardAuthenticator {
    private $em;
    private $appId;
    private $appSecret;
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $bag) {
        $this->em = $em;
        $this->appId = $bag->get("app_id");
        $this->appSecret = $bag->get("app_secret");

    }

    public function supports(Request $request) {
        // Is json and have openId field
        return empty($request->headers->get("Authorization")) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
    }

    public function getCredentials(Request $request) {
        $json = json_decode($request->getContent(), true);
        $token = $this->getToken($json["code"]);
        $userInfo = $this->decodeUserInfo($json["encrypted"], $json["iv"], $token);
        //var_dump($userInfo);
        return $userInfo;
    }

    private function getToken($code) {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appId."&secret=".$this->appSecret."&js_code=$code&grant_type=authorization_code";
        $client = new Client();
        $response = $client->get($url);
        $json = json_decode($response->getBody(), true);
        if ($json && !empty($json["session_key"])) {
            return $json["session_key"];
        }
        throw new \Exception("Unable to authenticate: ".$json["errmsg"]);
    }

    private function decodeUserInfo($cipher, $iv, $key) {
        $cipher = base64_decode($cipher);
        $iv = base64_decode($iv);
        $key = base64_decode($key);
        $data = json_decode(openssl_decrypt($cipher, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv), true);
        if ($data) {
            return $data;
        }
        throw new \Exception("Unable to decode user info.");
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        if ($credentials["openId"]) {
            $user = $this->em->getRepository(User::class)->findOneBy([
                "weChatOpenId" => $credentials["openId"]
            ]);
            if (!$user) {
                $user = new User();
                $user->setWeChatOpenId($credentials["openId"]);
                /* @var \App\Entity\Base\SecurityGroup $userGroup */
                $userGroup = $this->em->getRepository(SecurityGroup::class)->findOneBy([
                    "siteToken" => "ROLE_USER"
                ]);
                $userGroup->getChildren()->add($user);
                $this->em->persist($userGroup);
            }
            $user->setUsername($credentials["openId"]);
            $user->setFullName($credentials["nickName"]);
            $this->em->persist($user);
            $this->em->flush();
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