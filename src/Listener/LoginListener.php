<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 26/11/2018
 * Time: 3:59 PM
 */

namespace App\Listener;

use App\Entity\Core\WeChatUser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener {
    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $token = $event->getAuthenticationToken();
        if ($token instanceof PostAuthenticationGuardToken && $token->getProviderKey() === "main") {
            $user = $token->getUser();
            $this->session->set("userId", $user->getId());
            $now = new \DateTimeImmutable();
            $this->session->set("timestamp", $now->format("Y-m-d H:i:s"));
            if ($user instanceof WeChatUser) {
                $this->session->set("openId", $user->getWeChatOpenId());
            }
        }
    }
}