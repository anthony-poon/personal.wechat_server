<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 23/11/2018
 * Time: 11:23 AM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractStoreFront;
use App\FormType\Form\Core\AbstractStoreFrontForm;
use App\Service\EntityTableHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class StoreFrontController extends Controller {
    /**
     * @Route("/admin/store-fronts", name="store_front_list_store_fronts")
     */
    public function listStoreFronts(EntityTableHelper $helper, RouterInterface $router) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFronts = $repo->findAll();
        $helper->addButton("Edit", "store_front_edit");
        $helper->setHeader([
            "#",
            "Name",
            "Type",
            "Active",
            "Created"
        ]);
        $helper->setTitle("Store Fronts");
        foreach ($storeFronts as $storeFront) {
            /* @var AbstractStoreFront $storeFront */
            $helper->addRow($storeFront->getId(), [
                $storeFront->getId(),
                $storeFront->getName(),
                $storeFront->getType(),
                $storeFront->isDisabled() ? "False" : "True",
                $storeFront->getCreateDate()->format("Y-m-d")
            ]);
        }

        return $this->render("render/entity_table.html.twig",
            $helper->compile()
        );
    }

    /**
     * @Route("/admin/store-fronts/edit/{id}", name="store_front_edit")
     */
    public function edit(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFront = $repo->find($id);
        $form = $this->createForm(AbstractStoreFrontForm::class, $storeFront);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $storeItem = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($storeItem);
            $em->flush();
            return $this->redirectToRoute("store_front_list_store_fronts");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Edit Store Front",
            "form" => $form->createView(),
        ]);
    }
}