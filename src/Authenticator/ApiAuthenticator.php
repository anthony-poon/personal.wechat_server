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
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em) {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    public function supports(Request $request) {
        $json = json_decode($request->getContent(), true);
        $haveOpenId = !empty($json["openId"]) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
        $haveToken = !empty($request->headers->get("Authorization"));
        return $haveOpenId || $haveToken;
    }

    public function getCredentials(Request $request) {
        $json = json_decode($request->getContent(), true);
        $haveOpenId = !empty($json["openId"]) && "security_api_login" === $request->attributes->get("_route") && $request->isMethod("POST") && $request->getContentType() === "json";
        $haveToken = !empty($request->headers->get("Authorization"));
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
            default:
                throw new \Exception("Unsupported authentication method");
        }
    }

    public function checkCredentials($credentials, UserInterface $user) {
        switch ($credentials["type"]) {
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
            $gv = $repo->findOneBy([
                "key" => "visitorCount"
            ]);
            $count = (int) $gv->getValue() + 1;
            $gv->setValue($count);
            $this->em->persist($gv);
            $this->em->flush();
            return new JsonResponse([
                'login' => true,
                'token' => (string) $token,
                'visitorCount' => $count,
                'user' => $user->jsonSerialize()
            ]);
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