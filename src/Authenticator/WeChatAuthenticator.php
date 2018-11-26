<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 8/11/2018
 * Time: 11:35 AM
 */

namespace App\Authenticator;

use App\Entity\Base\SecurityGroup;
use App\Entity\Core\GlobalValue;
use App\Entity\Core\WeChatUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->appId = getenv("WECHAT_APP_ID");
        $this->appSecret = getenv("WECHAT_APP_SECRET");
    }

    public function supports(Request $request) {
        // Is json and have openId field
        return empty($request->headers->get("Authorization")) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
    }

    public function getCredentials(Request $request) {
        if (rand(0,10) > 5) {
            throw new \Exception("Random Error");
        }
        $json = json_decode($request->getContent(), true);
        $token = $this->getToken($json["code"]);
        $userInfo = $this->decodeUserInfo($json["encrypted"], $json["iv"], $token);
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
            $user = $this->em->getRepository(WeChatUser::class)->findOneBy([
                "weChatOpenId" => $credentials["openId"]
            ]);
            if (!$user) {
                $user = new WeChatUser();
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
        $user = $token->getUser();
        $session = new Session();
        $session->start();
        $session->set("openId", $user->getWeChatOpenId());
        $repo = $this->em->getRepository(GlobalValue::class);
        /* @var GlobalValue $gv */
        $gv = $repo->findOneBy([
            "key" => "visitorCount"
        ]);
        $count = (int) $gv->getValue() + 1;
        $gv->setValue($count);
        $this->em->persist($gv);
        $this->em->flush();
        return new JsonResponse([
            'login' => true,
            'sessionId' => $session->getId(),
            'visitorCount' => $count,
            'user' => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'username' => $user->getUsername(),
                'openId' => $user->getWeChatOpenId(),
            ]
        ]);
    }

    public function supportsRememberMe() {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        return new JsonResponse([
            "status" => "failure",
            "message" => "WeChat Authentication Required"
        ], Response::HTTP_UNAUTHORIZED);
    }


}