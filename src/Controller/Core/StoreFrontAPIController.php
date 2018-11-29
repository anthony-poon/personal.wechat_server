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
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Entity\Core\Ticketing\TicketingItem;
use App\Entity\Core\Ticketing\TicketingStoreFront;
use App\Service\JsonValidator;
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
    public function getStoreFronts(Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFronts = $repo->findAll();
        $showDisabled = $request->query->get("showDisabled") == true;
        /* @var AbstractStoreFront $storeFront */
        $rtn = [];
        foreach ($storeFronts as $storeFront) {
            if ($storeFront->isActive($showDisabled)) {
                $arr = $storeFront->jsonSerialize();
                if ($arr["asset"]) {
                    $arr["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $arr["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $rtn[] = $arr;
            }
        }
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/store-fronts/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getStoreFront(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFront = $repo->find($id);
        if (!$storeFront) {
            throw new NotFoundHttpException("Entity not found.");
        }
        $showTraded = $request->query->get("showTraded") == true;
        $showDisabled = $request->query->get("showDisabled") == true;
        $showExpired = $request->query->get("showExpired") == true;
        /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
        $storeItems = $storeFront->getStoreItems()
            ->filter(function(AbstractStoreItem $item) use ($showTraded, $showDisabled, $showExpired){
                return $item->isActive($showTraded, $showDisabled, $showExpired);
            })
            ->map(function(AbstractStoreItem $item) {
            $arr = $item->jsonSerialize();
            if ($arr["assets"]) {
                foreach (array_keys($arr["assets"]) as $key) {
                    $arr["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $arr["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
                }
            }
            return $arr;
        })->toArray();
        usort($storeItems, function($arr1, $arr2) {
            if ($arr1["isSticky"] xor $arr2["isSticky"]) {
                return -($arr1["isSticky"] <=> $arr2["isSticky"]);
            }
            return -($arr1["createDate"] <=> $arr2["createDate"]);
        });
        return new JsonResponse($storeItems);
    }

    /**
     * @Route("/api/store-fronts/{id}/store-items", methods={"POST"}, requirements={"id"="[\w_]+"})
     */
    public function createStoreItem(int $id, Request $request, JsonValidator $validator) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        /* @var AbstractStoreFront $storeFront */
        $storeFront = $repo->find($id);
        if (!$storeFront) {
            throw new NotFoundHttpException("Entity not found.");
        }
        $this->denyAccessUnlessGranted(StoreFrontVoter::UPDATE, $storeFront);
        $json = json_decode($request->getContent(), true);
        $constraints = [
            "name" => [
                new Assert\NotBlank()
            ],
            "description" => [
                new Assert\Optional()
            ],
            "weChatId" => [
                new Assert\Optional()
            ],
            "price" => [
                new Assert\GreaterThanOrEqual([
                    "value" => 0
                ]),
                new Assert\Type([
                    "type" => "numeric"
                ])
            ],
        ];
        switch (get_class($storeFront)) {
            case SecondHandStoreFront::class:
                $storeItem = new SecondHandItem();
                break;
            case HousingStoreFront::class:
                $storeItem = new HousingItem();
                $constraints["location"] = [
                    new Assert\NotBlank()
                ];
                $constraints["propertyType"] = [
                    new Assert\NotBlank()
                ];
                $constraints["durationDay"] = [
                    new Assert\GreaterThanOrEqual([
                        "value" => 0
                    ]),
                    new Assert\Type([
                        "type" => "integer"
                    ])
                ];
                break;
            case TicketingStoreFront::class:
                $storeItem = new TicketingItem();
                $constraints["effectiveDate"] = [
                    new Assert\Date([
                        "message" => "Invalid Date (yyyy-mm-dd)"
                    ])
                ];
                break;
            default:
                throw new \Exception("Unsupported Module");
                break;
        }
        $validator->setAllowExtraFields(true);
        /* @var $item \App\Entity\Core\AbstractStoreItem */
        $validator->validate($json, $constraints);
        $storeItem->setName($json["name"]);
        $storeItem->setDescription($json["description"] ?? null);
        $storeItem->setWeChatId($json["weChatId"] ?? null);
        $storeItem->setPrice($json["price"]);
        switch (get_class($storeItem)) {
            case SecondHandItem::class:
                break;
            case HousingItem::class:
                $storeItem->setDuration($json["durationDay"]);
                $storeItem->setPropertyType($json["propertyType"]);
                $storeItem->setLocation($json["location"]);
                break;
            case TicketingItem::class:
                $storeItem->setValidTill(\DateTimeImmutable::createFromFormat("Y-m-d", $json["createDate"]));
                break;
        }
        $storeItem->setStoreFront($storeFront);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->flush();
        return new JsonResponse($storeItem);
    }

}