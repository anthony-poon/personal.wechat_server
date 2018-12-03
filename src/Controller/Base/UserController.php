<?php

namespace App\Controller\Base;

use App\Entity\Base\User;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\WeChatUser;
use App\FormType\Form\Core\WeChatUserForm;
use App\FormType\Form\Users\CreateUsersForm;
use App\FormType\Form\Users\EditUsersForm;
use App\Service\BaseTemplateHelper;
use App\Service\EntityTableHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller {
    public function __construct(BaseTemplateHelper $helper, RouterInterface $router) {
        $helper->setTitle("Administration");
    }

    /**
     * @Route("/admin", name="user_index")
     */
    public function index() {
        return $this->redirectToRoute("admin_list_user");
    }

    /**
     * @Route("/admin/users", name="user_list")
     */
    public function list(EntityTableHelper $helper, RouterInterface $router) {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        $helper->addButton("Edit", "user_edit");
        $helper->addButton("Delete", "user_delete");
        $helper->addButton("View Items", "store_item_list_store_items", [
            "user"
        ]);
        $helper->setHeader([
        	"#",
			"Username",
			"Full Name",
			"Email"
		]);
        $helper->setTitle("Users");
        foreach ($users as $u) {
        	/* @var WeChatUser $u */
            $helper->addRow($u->getId(), [
                $u->getId(),
                $u->getUsername(),
                $u->getFullName(),
                $u->getEmail()
            ], [
                "user" => $u->getId()
            ]);
		}

        return $this->render("render/entity_table.html.twig", $helper->compile());
    }

    /**
     * @Route("/admin/users/create", name="user_create")
     */
    public function create(Request $request, UserPasswordEncoderInterface $encoder) {
        $user = new User();
        $form = $this->createForm(CreateUsersForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var \App\Entity\Base\User $user */
            $user = $form->getData();
            $pw = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($pw);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("user_list");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Create User",
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/users/{id}", name="user_edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, UserPasswordEncoderInterface $encoder, int $id) {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->find($id);
        if (!$user) {
            throw new NotFoundHttpException("Unable to locate entity.");
        }
        switch (get_class($user)) {
            case WeChatUser::class:
                $form = $this->createForm(WeChatUserForm::class, $user);
                break;
            case User::class:
                $form = $this->createForm(EditUsersForm::class, $user);
                break;
            default:
                throw new \Exception("Unsupported Method");
                break;
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var \App\Entity\Base\User $user */
            $user = $form->getData();
            $pw = $user->getPlainPassword();
            if ($pw) {
                $pw = $encoder->encodePassword($user, $pw);
                $user->setPassword($pw);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("user_list");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Edit User",
            "form" => $form->createView()
        ]);
    }

	/**
	 * @Route("/admin/users/delete/{id}", name="user_delete")
	 */
	public function deleteUser(int $id) {
		$repo = $this->getDoctrine()->getRepository(User::class);
		$user = $repo->find($id);
		if (!$user) {
			throw new NotFoundHttpException("Unable to locate entity.");
		}
		$em = $this->getDoctrine()->getManager();
		$em->remove($user);
		$em->flush();
		return $this->redirectToRoute("user_list");
	}


}
