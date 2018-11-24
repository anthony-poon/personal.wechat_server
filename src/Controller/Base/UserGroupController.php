<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 27/10/2018
 * Time: 11:24 PM
 */

namespace App\Controller\Base;

use App\Entity\Base\DirectoryGroup;
use App\Entity\Base\SecurityGroup;
use App\FormType\Form\UserGroups\ChooseUserGroupsTypeForm;
use App\FormType\Form\UserGroups\DirectoryGroupsForm;
use App\FormType\Form\UserGroups\SecurityGroupForm;
use App\Service\BaseTemplateHelper;
use App\Service\EntityTableHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserGroupController extends Controller {
    /**
     * @Route("/admin/user-groups", name="user_group_list")
     */
    public function list(EntityTableHelper $helper, RouterInterface $router) {
        $grpRepo = $this->getDoctrine()->getRepository(DirectoryGroup::class);
        $grps = $grpRepo->findAll();
        $helper->setHeader([
            "#",
            "Group Name",
            "Group Type",
            "Member count"
        ]);
        foreach ($grps as $g) {
            /* @var DirectoryGroup $g */
            $helper->addRow($g->getId(), [
                $g->getId(),
                $g->getName(),
                $g->getFriendlyClassName(),
                $g->getChildren()->count()
            ]);
        }
        $helper->setAddPath("user_group_create");
        $helper->setEditPath("user_group_edit");
        $helper->setDelPath("user_group_delete");
        $helper->setTitle("User Groups");
        return $this->render("render/entity_table.html.twig", $helper->compile());
    }

    /**
     * @Route("/admin/user-groups/create", name="user_group_create")
     */
    public function create(BaseTemplateHelper $helper, Request $request) {
        $formType = $request->get("t");
        $em = $this->getDoctrine()->getManager();
        switch ($formType) {
            case "directory_group":
                $form = $this->createForm(DirectoryGroupsForm::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $group = $form->getData();
                    $em->persist($group);
                    $em->flush();
                    return $this->redirectToRoute("user_group_list");
                }
                break;
            case "security_group":
                $form = $this->createForm(SecurityGroupForm::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $group = $form->getData();
                    $em->persist($group);
                    $em->flush();
                    return $this->redirectToRoute("user_group_list");
                }
                break;
            default:
                $form = $this->createForm(ChooseUserGroupsTypeForm::class);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $formType = $form->getData()["groupType"];
                    return $this->redirectToRoute("user_group_create", ["t" => $formType]);
                }
                break;
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Create Group",
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user-groups/{id}", name="user_group_edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, int $id) {
        $em = $this->getDoctrine()->getManager();
        $groupRepo = $this->getDoctrine()->getRepository(DirectoryGroup::class);
        $group = $groupRepo->find($id);
        if (empty($group)) {
            throw new NotFoundHttpException("Unable to locate entity.");
        }
        switch (get_class($group)) {
            case SecurityGroup::class:
                $form = $this->createForm(SecurityGroupForm::class, $group);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $group = $form->getData();
                    $em->persist($group);
                    $em->flush();
                    $this->redirectToRoute("user_group_list");
                }
                break;
            // Catch all
            default:
                $form = $this->createForm(DirectoryGroupsForm::class, $group);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $group = $form->getData();
                    $em->persist($group);
                    $em->flush();
                    $this->redirectToRoute("user_group_list");
                }
                break;
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Edit Group",
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user-groups/delete/{id}", name="user_group_delete")
     */
    public function ApiDeleteUserGroup(int $id) {
        $repo = $this->getDoctrine()->getRepository(DirectoryGroup::class);
        $grp = $repo->find($id);
        if (!$grp) {
            throw new NotFoundHttpException("Unable to locate entity.");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($grp);
        $em->flush();
        return $this->redirectToRoute("user_group_list");
    }
}