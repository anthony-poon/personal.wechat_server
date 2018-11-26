<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 3:10 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;;

use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Entity\Core\Ticketing\TicketingItem;
use App\Entity\Core\Ticketing\TicketingStoreFront;
use App\Voter\StoreFrontVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        /* @var AbstractStoreFront $storeFront */
        $rtn = [];
        foreach ($storeFronts as $storeFront) {
            $arr = $storeFront->jsonSerialize();
            if ($arr["asset"]) {
                $arr["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $arr["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $rtn[] = $arr;
        }
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/store-fronts/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getStoreFront(int $id) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFront = $repo->find($id);
        if (!$storeFront) {
            throw new NotFoundHttpException("Entity not found.");
        }
        /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
        $storeItems = $storeFront->getStoreItems()->map(function(AbstractStoreItem $item) {
            $arr = $item->jsonSerialize();
            if ($arr["assets"]) {
                foreach (array_keys($arr["assets"]) as $key) {
                    $arr["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $arr["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
                }
            }
            return $arr;
        })->toArray();
        usort($storeItems, function($arr1, $arr2) {
            if ($arr1["isSticky"] xor $arr2["isPremium"]) {
                return -($arr1["isSticky"] <=> $arr2["isSticky"]);
            }
            return -($arr1["createDate"] <=> $arr2["createDate"]);
        });
        return new JsonResponse($storeItems);
    }

    /**
     * @Route("/api/store-fronts/{id}/store-items", methods={"POST"}, requirements={"id"="[\w_]+"})
     */
    public function createStoreItem(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        /* @var AbstractStoreFront $storeFront */
        $storeFront = $repo->find($id);
        if (!$storeFront) {
            throw new NotFoundHttpException("Entity not found.");
        }
        $this->denyAccessUnlessGranted(StoreFrontVoter::UPDATE, $storeFront);
        $json = json_decode($request->getContent(), true);
        switch (get_class($storeFront)) {
            case SecondHandStoreFront::class:
                $storeItem = new SecondHandItem();
                break;
            case HousingStoreFront::class:
                $storeItem = new HousingItem();
                $storeItem->setDuration($json["duration"]);
                $storeItem->setPropertyType($json["propertyType"]);
                $storeItem->setLocation($json["location"]);
                break;
            case TicketingStoreFront::class:
                $storeItem = new TicketingItem();
                $storeItem->setValidTill(\DateTimeImmutable::createFromFormat("Y-m-d", $json["validTill"]));
                break;
            default:
                throw new \Exception("Unsupported Module");
                break;
        }
        /* @var $item \App\Entity\Core\AbstractStoreItem */
        $storeItem->setStoreFront($storeFront);
        $storeItem->setVisitorCount(0);
        $storeItem->setIsTraded(false);
        $storeItem->setPrice((float) $json["price"]);
        $storeItem->setName($json["name"]);
        $storeItem->setDescription($json["description"]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->flush();
        return new JsonResponse($storeItem);
    }

}