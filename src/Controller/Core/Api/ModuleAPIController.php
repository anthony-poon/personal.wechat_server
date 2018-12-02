<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 2:38 PM
 */

namespace App\Controller\Core\Api;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Entity\Core\Ticketing\TicketingItem;
use App\Entity\Core\Ticketing\TicketingModule;
use App\Entity\Core\Ticketing\TicketingStoreFront;
use App\Entity\Core\WeChatUser;
use App\Exception\ValidationException;
use App\Service\JsonValidator;
use App\Voter\StoreFrontVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ModuleAPIController extends Controller {
    /**
     * @Route("/api/modules", methods={"GET"})
     */
    public function getModules() {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $modules = $repo->findAll();
        return new JsonResponse($modules);
    }

    /**
     * @Route("/api/modules/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getModule(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $module = $repo->find($id);
        /* @var AbstractModule $module */
        $showDisabled = $request->query->get("showDisabled") == true;
        $rtn = $module->getStoreFronts()
            ->filter(function(AbstractStoreFront $storeFront) use ($showDisabled){
                return $storeFront->isActive($showDisabled);
            })
            ->map(function(AbstractStoreFront $storeFront) {
                return $storeFront->jsonSerialize();
            })->toArray();
        foreach (array_keys($rtn) as $key) {
            if ($rtn[$key]["asset"]) {
                $rtn[$key]["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $rtn[$key]["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
            }
        };
        usort($rtn, function($arr1, $arr2) {
            if ($arr1["isSticky"] xor $arr2["isSticky"]) {
                return -($arr1["isSticky"] <=> $arr2["isSticky"]);
            }
            return -($arr1["createDate"] <=> $arr2["createDate"]);
        });
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/modules/{id}/store-items", methods={"POST"}, requirements={"id"="[\w_]+"})
     * @throws ValidationException
     */
    public function createStoreItem(int $id, Request $request, JsonValidator $validator) {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        /* @var AbstractModule $module */
        $module = $repo->find($id);
        if (!$module) {
            throw new NotFoundHttpException("Entity not found.");
        }
        // Get storeFront by user, default current logged in user
        /* @var WeChatUser $user */
        $user = $this->getUser();
        $storeFront = $module->getStoreFronts()->filter(function(AbstractStoreFront $storeFront) use ($user) {
            return $storeFront->getOwner() === $user;
        })->first();
        if (empty($storeFront)) {
            switch (get_class($module)) {
                case SecondHandModule::class:
                    $storeFront = new SecondHandStoreFront();
                    break;
                case HousingModule::class:
                    $storeFront = new HousingStoreFront();
                    break;
                case TicketingModule::class:
                    $storeFront = new TicketingStoreFront();
                    break;
                default:
                    throw new \Exception("Unsupported Module");
                    break;
            }
            $storeFront->setName($user->getFullName());
            $storeFront->setOwner($user);
            $storeFront->setModule($module);
            $this->getDoctrine()->getManager()->persist($storeFront);
        }
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
            ]
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
                $constraints["duration"] = [
                    new Assert\NotBlank()
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
                $storeItem->setDuration($json["duration"]);
                $storeItem->setPropertyType($json["propertyType"]);
                $storeItem->setLocation($json["location"]);
                break;
            case TicketingItem::class:
                $storeItem->setValidTill(\DateTimeImmutable::createFromFormat("Y-m-d", $json["effectiveDate"]));
                break;
        }
        $storeItem->setStoreFront($storeFront);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->flush();
        return new JsonResponse($storeItem);
    }
}