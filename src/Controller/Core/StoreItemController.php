<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 23/11/2018
 * Time: 11:23 AM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractStoreItem;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\Ticketing\TicketingItem;
use App\FormType\Form\Core\HousingItemForm;
use App\FormType\Form\Core\SecondHandItemForm;
use App\FormType\Form\Core\TicketingItemForm;
use App\Service\EntityTableHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class StoreItemController extends Controller {
    /**
     * @Route("/admin/store-items", name="store_item_list_store_items")
     */
    public function listStoreItems(EntityTableHelper $helper, RouterInterface $router) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        $storeItems = $repo->findAll();
        //$helper->setAddPath("store_item_create");
        //$helper->setDelPath("store_item_delete");
        $helper->setEditPath("store_item_edit");
        $helper->setHeader([
            "#",
            "Name",
            "Type",
            "Active",
            "Traded",
            "Visitors",
            "Created"
        ]);
        $helper->setTitle("Store Item");
        foreach ($storeItems as $storeItem) {
            /* @var AbstractStoreItem $storeItem */
            $helper->addRow($storeItem->getId(), [
                $storeItem->getId(),
                $storeItem->getName(),
                $storeItem->getType(),
                $storeItem->isDisabled() ? "True" : "False",
                $storeItem->isTraded() ? "True" : "False",
                $storeItem->getVisitorCount() + $storeItem->getVisitorCountModification(),
                $storeItem->getCreateTime()->format("Y-m-d")
            ]);
        }

        return $this->render("render/entity_table.html.twig",
            $helper->compile()
        );
    }

    /**
     * @Route("/admin/store-items/edit/{id}", name="store_item_edit")
     */
    public function edit(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        /* @var \App\Entity\Core\SecondHand\SecondHandItem $storeItem */
        $storeItem = $repo->find($id);
        switch (get_class($storeItem)) {
            case SecondHandItem::class:
                $form = $this->createForm(SecondHandItemForm::class, $storeItem);
                break;
            case HousingItem::class:
                $form = $this->createForm(HousingItemForm::class, $storeItem);
                break;
            case TicketingItem::class:
                $form = $this->createForm(TicketingItemForm::class, $storeItem);
                break;
            default;
                throw new \Exception("Unsupported Method");
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $storeItem = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($storeItem);
            $em->flush();
            return $this->redirectToRoute("store_item_list_store_items");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Edit Item",
            "form" => $form->createView(),
            "additional_template" => [
                "path" => "component/view/store_items/btn_bar.html.twig"
            ]
        ]);
    }
}