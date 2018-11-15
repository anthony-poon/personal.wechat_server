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
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\Ticketing\TicketingItem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StoreFrontAPIController extends Controller {
    /**
     * @Route("/api/store-fronts", methods={"GET"})
     */
    public function getStoreFronts(ParameterBagInterface $bag) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFronts = $repo->findAll();
        $rtn = [];
        foreach ($storeFronts as $storeFront) {
            /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
            $rtn[] = [
                "id" => $storeFront->getPaddedId(),
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
     * @Route("/api/store-fronts/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getStoreFront(string $id, ParameterBagInterface $bag) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        preg_match("/^\D*0*(\d+)$$/", $id, $match);
        $id = $match[1];
        $storeFront = $repo->find($id);
        if (!$storeFront) {
            throw new NotFoundHttpException("Entity not found.");
        }
        /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
        $rtn = [
            "status" => "success",
            "id" => $storeFront->getPaddedId(),
            "name" => $storeFront->getName(),
            "storeItems" => []
        ];
        foreach ($storeFront->getStoreItems() as $storeItem) {
            /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
            $arr = [
                "id" => $storeItem->getPaddedId(),
                "name" => $storeItem->getName(),
                "description" => $storeItem->getDescription(),
                "price" => $storeItem->getPrice(),
                "visitorCount" => $storeItem->getVisitorCount() + $storeItem->getVisitorCountModification(),
                "assets" => $storeItem->getAssets()->map(function(Asset $asset){
                    return $this->generateUrl("api_asset_get_item", [
                        "id" => $asset->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                })->toArray(),
                "attr" => [
                ]
            ];
            switch (get_class($storeItem)) {
                case SecondHandItem::class:
                    /* @var SecondHandItem $storeItem */
                    break;
                case HousingItem::class:
                    /* @var HousingItem $storeItem */
                    $arr["attr"]["location"]["label"] = "地區";
                    $arr["attr"]["location"]["value"] = $storeItem->getLocation();
                    $arr["attr"]["propertyType"]["label"] = "房形";
                    $arr["attr"]["propertyType"]["value"] = $storeItem->getPropertyType();
                    $arr["attr"]["durationDay"]["label"] = "時長";
                    $arr["attr"]["durationDay"]["value"] = $storeItem->getDuration();
                    break;
                case TicketingItem::class:
                    /* @var TicketingItem $storeItem */
                    $arr["attr"]["validTill"]["label"] = "有效期";
                    $arr["attr"]["validTill"]["value"] = $storeItem->getValidTill()->format("Y-m-d");
                    break;
            }
            $rtn["storeItems"][] = $arr;
        }
        return new JsonResponse($rtn);
    }

}