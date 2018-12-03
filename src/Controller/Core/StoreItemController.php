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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class StoreItemController extends Controller {
    /**
     * @Route("/admin/store-items", name="store_item_list_store_items")
     */
    public function listStoreItems(EntityTableHelper $helper, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        $queries = $request->query->all();
        $storeItems = $repo->findAll();
        foreach ($queries as $key => $value) {
            switch ($key) {
                case "storeFront":
                    $storeItems = array_filter($storeItems, function(AbstractStoreItem $storeItem) use ($value){
                        return $storeItem->getStoreFront()->getId() == $value;
                    });
                    break;
                case "user":
                    $storeItems = array_filter($storeItems, function(AbstractStoreItem $storeItem) use ($value){
                        return $storeItem->getStoreFront()->getOwner()->getId() == $value;
                    });
                    break;
            }
        }
        $helper->addButton("Edit", "store_item_edit");
        $helper->addButton("Edit Assets", "store_item_edit_assets");
        $helper->setHeader([
            "#",
            "Name",
            "Type",
            "Location",
            "Store Front Id",
            "Module Id",
            "Created"
        ]);
        $helper->setTitle("Store Item");
        foreach ($storeItems as $storeItem) {
            /* @var AbstractStoreItem $storeItem */
            $helper->addRow($storeItem->getId(), [
                $storeItem->getId(),
                $storeItem->getName(),
                $storeItem->getType(),
                $storeItem->getStoreFront()->getModule()->getLocation()->getName(),
                $storeItem->getStoreFront()->getId(),
                $storeItem->getStoreFront()->getModule()->getId(),
                $storeItem->getCreateDate()->format("Y-m-d")
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
        ]);
    }

    /**
     * @Route("/admin/store-items/edit/{id}/assets", name="store_item_edit_assets")
     */
    public function editAssets(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        /* @var AbstractStoreItem $storeItem */
        $storeItem = $repo->find($id);
        $assets = $storeItem->getAssets();
        $rtn = [
            "storeItemId" => $id
        ];
        foreach ($assets as $asset) {
            /* @var \App\Entity\Base\Asset $asset */
            $rtn["assets"][] = [
                "id" => $asset->getId(),
                "ownerId" => $storeItem->getStoreFront()->getOwner()->getId(),
                "owner" => $storeItem->getStoreFront()->getOwner()->getFullName(),
                "type" => $storeItem->getType(),
                "createDate" => $asset->getCreateDate()->format("Y-m-d H:i:s")
            ];
        }
        return $this->render("render/store_items/edit_assets.html.twig", $rtn);
    }
}