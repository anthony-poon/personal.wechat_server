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
use App\Entity\Core\GlobalValue;
use App\Entity\Core\WeChatUser;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Lcobucci\JWT\Builder;

class ApiAuthenticator extends AbstractGuardAuthenticator {
    private $encoder;
    private $em;
    private $appId;
    private $appSecret;
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em) {
        $this->encoder = $encoder;
        $this->em = $em;
        $this->appId = getenv("WECHAT_APP_ID");
        $this->appSecret = getenv("WECHAT_APP_SECRET");
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

    public function supports(Request $request) {
        $json = json_decode($request->getContent(), true);
        $haveOpenId = !empty($json["openId"]) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
        $haveToken = !empty($request->headers->get("Authorization"));
        $haveSession = !empty($request->getSession()->get("userId"));
        $haveRealLogin = !empty($json["code"]) && !empty($json["encrypted"]) && !empty($json["iv"]) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
        return $haveOpenId || $haveToken || $haveSession || $haveRealLogin;
    }

    public function getCredentials(Request $request) {
        $json = json_decode($request->getContent(), true);
        $haveOpenId = !empty($json["openId"]) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
        $haveToken = !empty($request->headers->get("Authorization"));
        $haveSession = !empty($request->getSession()->get("userId"));
        $haveRealLogin = !empty($json["code"]) && !empty($json["encrypted"]) && !empty($json["iv"]) ;
        preg_match("/Bearer (.+)$/", $request->headers->get("Authorization"), $match);
        if ($haveOpenId) {
            return [
                "type" => "openId",
                "openId" => $json["openId"],
            ];
        }
        if ($haveToken) {
            return [
                "type" => "token",
                "jwt" => $match[1],
                "issuer" => $request->getSchemeAndHttpHost(),
                "audience" => $request->getClientIp()
            ];
        }
        if ($haveRealLogin) {
            $token = $this->getToken($json["code"]);
            $userInfo = $this->decodeUserInfo($json["encrypted"], $json["iv"], $token);
            return [
                "type" => "wxAuth",
                "userInfo" => $userInfo
            ];
        }
        if ($haveSession) {
            return [
                "type" => "session",
                "userId" => $request->getSession()->get("userId")
            ];
        }
        return false;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        switch ($credentials["type"]) {
            case "openId":
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
                $user->setFullName($credentials["openId"]);
                $this->em->persist($user);
                $this->em->flush();
                return $user;
            case "token":
                $token = (new Parser())->parse($credentials["jwt"]);
                $userId = $token->getClaim("userId");
                $user = $this->em->getRepository(User::class)->find($userId);
                return $user;
            case "session":
                $userId = $credentials["userId"];
                $user = $this->em->getRepository(User::class)->find($userId);
                return $user;
            case "wxAuth":
                $user = $this->em->getRepository(WeChatUser::class)->findOneBy([
                    "weChatOpenId" => $credentials["userInfo"]["openId"]
                ]);
                if (!$user) {
                    $user = new WeChatUser();
                    $user->setWeChatOpenId($credentials["userInfo"]["openId"]);
                    /* @var \App\Entity\Base\SecurityGroup $userGroup */
                    $userGroup = $this->em->getRepository(SecurityGroup::class)->findOneBy([
                        "siteToken" => "ROLE_USER"
                    ]);
                    $userGroup->getChildren()->add($user);
                    $this->em->persist($userGroup);
                }
                $user->setUsername($credentials["userInfo"]["openId"]);
                $user->setFullName($credentials["userInfo"]["openId"]);
                $this->em->persist($user);
                $this->em->flush();
                return $user;
            default:
                throw new \Exception("Unsupported authentication method");
        }
    }

    public function checkCredentials($credentials, UserInterface $user) {
        switch ($credentials["type"]) {
            case "wxAuth":
            case "session":
            case "openId":
                return true;
            case "token":
                $signer = new Sha256();
                $token = (new Parser())->parse($credentials["jwt"]);
                $data = new ValidationData();
                $data->setIssuer($credentials["issuer"]);
                $data->setAudience($credentials["audience"]);
                $unaltered= $token->verify($signer, getenv("APP_SECRET"));
                $valid = $token->validate($data);
                return $unaltered && $valid;
            default:
                return false;
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse([
            "status" => "failure",
            "message" => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ], Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        // isLogin
        if ("security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json") {
            /* @var User $user */
            $appSecret = getenv("APP_SECRET");
            $user = $token->getUser();
            $signer = new Sha256();
            $token = (new Builder())->setIssuer($request->getSchemeAndHttpHost())
                ->setAudience($request->getClientIp())
                ->setId(bin2hex(random_bytes(5)), true)
                ->setIssuedAt(time())
                ->setExpiration(time() + 36000)
                ->set("userId", $user->getId())
                ->sign($signer, $appSecret)
                ->getToken();
            $repo = $this->em->getRepository(GlobalValue::class);
            $globalValues = $repo->findAll();
            $rtn = [
                'login' => true,
                'token' => (string) $token,
                'user' => $user->jsonSerialize(),
                'config' => []
            ];
            $count = 0;
            $countMod = 0;
            foreach ($globalValues as $value) {
                /* @var \App\Entity\Core\GlobalValue $value */
                switch ($value->getKey()) {
                    case "visitorCount":
                        $value->setValue($value->getValue() + 1);
                        $this->em->persist($value);
                        $count = (int) $value->getValue();
                        break;
                    case "visitorCountMod":
                        $countMod = (int) $value->getValue();
                        break;
                    default:
                        $rtn["config"][$value->getKey()] = $value->getValue();
                        break;
                }

            }
            $rtn["config"]["visitorCount"] = $count + $countMod;
            $this->em->flush();
            return new JsonResponse($rtn);
        }
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