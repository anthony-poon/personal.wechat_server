<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 3:10 PM
 */

namespace App\Controller\Core;

use App\Entity\Base\Asset;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Entity\Core\Ticketing\TicketingItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StoreFrontAPIController extends Controller {
    /**
     * @Route("/api/store-fronts", methods={"GET"})
     */
    public function getStoreFronts() {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFronts = $repo->findAll();
        $rtn = [];
        foreach ($storeFronts as $storeFront) {
            /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
            $rtn[] = [
                "id" => $storeFront->getId(),
                "name" => $storeFront->getName(),
                "owner" => $storeFront->getOwner()->getFullName()
            ];
        }
        return new JsonResponse([
            "status" => "success",
            "storeFronts" => $rtn
        ]);
    }

    /**
     * @Route("/api/store-fronts/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getStoreFront(int $id) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFront = $repo->find($id);
        /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
        $rtn = [
            "status" => "success",
            "id" => $storeFront->getId(),
            "name" => $storeFront->getName(),
            "storeItems" => []
        ];
        foreach ($storeFront->getStoreItems() as $storeItem) {
            /* @var \App\Entity\Core\AbstractStoreItem $storeItem */

            $arr = [
                "id" => $storeItem->getId(),
                "name" => $storeItem->getName(),
                "description" => $storeItem->getDescription(),
                "price" => $storeItem->getPrice(),
                "visitorCount" => $storeItem->getVisitorCount() + $storeItem->getVisitorCountModification(),
                "assets" => $storeItem->getAssets()->map(function(Asset $asset){
                    return $this->generateUrl("api_asset_get_item", [
                        "id" => $asset->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                })->toArray(),
            ];
            switch (get_class($storeItem)) {
                case SecondHandItem::class:
                    /* @var SecondHandItem $storeItem */
                    $arr["itemType"] = "SecondHandItem";
                    break;
                case HousingItem::class:
                    /* @var HousingItem $storeItem */
                    $arr["itemType"] = "HousingItem";
                    $arr["location"] = $storeItem->getLocation();
                    $arr["propertyType"] = $storeItem->getPropertyType();
                    $arr["durationDay"] = $storeItem->getDuration();
                    break;
                case TicketingItem::class:
                    /* @var TicketingItem $storeItem */
                    $arr["itemType"] = "TicketingItem";
                    $arr["validTill"] = $storeItem->getValidTill()->format("Y-m-d");
                    break;
            }
            $rtn["storeItems"][] = $arr;
        }
        return new JsonResponse($rtn);
    }

}