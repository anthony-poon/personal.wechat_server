<?php
namespace App\Service;

use App\Entity\Base\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseTemplateHelper {
    private $sideMenu = [];
    private $sideMenuStyle;
    private $navMenu = [];
    private $layout;
    private $title = "Web Application";
    private $jsParam = [];
    /* @var User $user */
	private $user = null;
	private $css = [];
	private $js = [];
	private $role = [];
    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage, ParameterBagInterface $params) {
        $this->sideMenuStyle = $params->get("side_menu_style");
        $this->layout = $params->get("layout");
		$token = $tokenStorage->getToken();
		$this->role = [];
		if ($token) {
			$this->user = $token->getUser();
			if ($this->user && $this->user instanceof User) {
				$this->role = $this->user->getRoles();
			}
		}

//    	$this->navMenu = [
//        	[
//        		"text" => "Home",
//				"icon" => "home",
//				"url" => $router->generate("home"),
//			], [
//				"text" => "Users",
//				"isVisible" => in_array("ROLE_ADMIN", $this->role),
//                "url" => $router->generate("user_list"),
//			], [
//			    "text" => "Store Items",
//                "isVisible" => in_array("ROLE_ADMIN", $this->role),
//                "url" => $router->generate("store_item_list_store_items")
//            ]
//		];

        $this->sideMenu = [
            [
                "text" => "Home",
                "icon" => "home",
                "url" => $router->generate("home"),
            ], [
                "text" => "Users",
                "isVisible" => in_array("ROLE_ADMIN", $this->role),
                "url" => $router->generate("user_list"),
            ], [
                "text" => "Store Fronts",
                "isVisible" => in_array("ROLE_ADMIN", $this->role),
                "url" => $router->generate("store_front_list_store_fronts")
            ], [
                "text" => "Store Items",
                "isVisible" => in_array("ROLE_ADMIN", $this->role),
                "url" => $router->generate("store_item_list_store_items")
            ], [
                "text" => "Global Value",
                "isVisible" => in_array("ROLE_ADMIN", $this->role),
                "url" => $router->generate("global_value_edit")
            ], [
                "text" => "Sticky Ticket",
                "isVisible" => in_array("ROLE_ADMIN", $this->role),
                "url" => $router->generate("sticky_ticket_list_tickets")
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNavMenu(): array {
        return $this->navMenu;
    }

    public function addNavMenu(array $item) {
        $this->navMenu[] = $item;
        return $this;
    }

    public function setNavMenu(array $menu) {
        $this->navMenu = $menu;
    }

    /**
     * @return array
     */
    public function getSideMenu(): array {
        return $this->sideMenu;
    }

    public function addSideMenu(array $item) {
        $this->sideMenu[] = $item;
        return $this;
    }

    public function setSideMenu(array $menu) {
        $this->sideMenu = $menu;
    }

    /**
     * @return string
     */
    public function getSideMenuStyle(): string
    {
        return $this->sideMenuStyle;
    }

    /**
     * @param mixed $sideMenuStyle
     */
    public function setSideMenuStyle($sideMenuStyle) {
        $this->sideMenuStyle = $sideMenuStyle;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     * @return BaseTemplateHelper
     */
    public function setTitle(string $title): BaseTemplateHelper {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getJsParam(): array {
        return $this->jsParam;
    }

    /**
     * @param array $jsParam
     * @return BaseTemplateHelper
     */
    public function addJsParam(array $jsParam): BaseTemplateHelper {
        $this->jsParam = array_merge($jsParam, $this->jsParam);
        return $this;
    }

    public function addJs($js): BaseTemplateHelper {
    	$this->js[] = $js;
    	return $this;
	}

	public function addCss($css): BaseTemplateHelper {
    	$this->css[] = $css;
    	return $this;
	}

	/**
	 * @return array
	 */
	public function getCss(): array {
		return $this->css;
	}

	/**
	 * @return array
	 */
	public function getJs(): array {
		return $this->js;
	}

    /**
     * @return mixed
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    public function setLayout($layout) {
        $this->layout = $layout;
    }


}