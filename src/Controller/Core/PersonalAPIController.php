<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 13/11/2018
 * Time: 3:04 PM
 */

namespace App\Controller\Core;

use App\Entity\Base\User;
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
use App\Exception\ValidationException;
use App\Service\JsonValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;

class PersonalAPIController extends Controller {
    /**
     * @Route("/api/personal", methods={"GET"})
     */
    public function getPersonal() {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        /* @var \App\Entity\Base\User $user */
        $user = $this->getUser();
        $rtn = [
            "id" => $user->getId(),
            "openId" => $user->getWeChatOpenId(),
            "fullName" => $user->getFullName(),
            "stores" => []
        ];
        foreach ($user->getStores() as $store) {
            /* @var \App\Entity\Core\AbstractStoreFront $store */
            $rtn["stores"][] = [
                "id" => $store->getId(),
                "name" => $store->getName(),
                "moduleId" => $store->getModule()->getId(),
                "moduleName" => $store->getModule()->getName(),
                "location" => $store->getModule()->getLocation()->getName(),
                "locationId" => $store->getModule()->getLocation()->getId(),
            ];
        }
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/personal/modules/{id}/store-fronts", methods={"POST"}, requirements={"id"="[\w_]+"})
     * @throws ValidationException
     * @throws \Exception
     */
    public function createStoreFront(string $id, Request $request, JsonValidator $validator) {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        /* @var \App\Entity\Core\AbstractModule $module */
        preg_match("/^\D*0*(\d+)$$/", $id, $match);
        $id = $match[1];
        $module = $repo->find($id);
        $user = $this->getUser();
        if ($module->getStoreFronts()->exists(function(int $key, AbstractStoreFront $storeFront) use ($user){
            return $storeFront->getOwner() === $user;
        })) {
            throw new \Exception("Current user already have a store front in the module");
        }
        $json = json_decode($request->getContent(), true);
        $validator->validate($json, [
            "name" => new Assert\NotBlank()
        ]);
        $storeFront = $this->createStoreFrontEntity($module, $user, $json);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeFront);
        $em->flush();
    }


    /**
     * @Route("/api/personal/store-items", methods={"POST"})
     * @throws \Exception
     * @throws ValidationException
     */
    public function createStoreItem(Request $request) {
        /* @var User $user */
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $user = $this->getUser();
        $id = $request->query->get("id");
        preg_match("/^(\D)*0*(\d+)$/", $id, $match);
        $prefix = $match[1];
        $id = $match[2];
        if (substr($prefix,0 ,1) === "M") {
            // ID is a module id
            /* @var AbstractModule $module */
            $module = $this->getDoctrine()->getRepository(AbstractModule::class)->find($id);
            if ($module) {
                // Check if owner have a store in the module, if not create one
                $storeFront = $module->getStoreFronts()->filter(function(AbstractStoreFront $storeFront) use ($user){
                    return $storeFront->getOwner() === $user;
                });
                if (!$storeFront) {
                    $storeFront = $this->createStoreFrontEntity($module, $user, [
                        "name" => $user->getFullName()
                    ]);
                }
            }
        } else {
            $storeFront = $this->getDoctrine()->getRepository(AbstractStoreFront::class)->find($id);
        }
        if (empty($storeFront)) {
            throw new NotFoundHttpException("Entity not found.");
        }
        // Need to validate data later
        switch ($request->getContentType()) {
            case "json":
                $data = json_decode($request->getContent(), true);
                break;
            default:
                $data = $request->request->all();
                break;
        }
        $storeItem = $this->createStoreItemEntity($storeFront, $data);
        /* @var $item \App\Entity\Core\AbstractStoreItem */
        $storeItem->setStoreFront($storeFront);
        $storeItem->setVisitorCount(0);
        $storeItem->setIsTraded(false);
        $storeItem->setPrice((float)$data["price"]);
        $storeItem->setName($data["name"]);
        $storeItem->setDescription($data["description"]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeFront);
        $em->persist($storeItem);
        $em->flush();
    }

    private function createStoreFrontEntity(AbstractModule $module, User $user, array $data): AbstractStoreFront {
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
                throw new \Exception("Unsupported type");
                break;
        }
        /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
        $storeFront->setModule($module);
        $storeFront->setName($data["name"]);
        $storeFront->setOwner($user);
        return $storeFront;
    }

    private function createStoreItemEntity(AbstractStoreFront $storeFront, array $data): AbstractStoreItem {
        switch (get_class($storeFront)) {
            case SecondHandStoreFront::class:
                /* @var SecondHandStoreFront $storeFront */
                $item = new SecondHandItem();
                break;
            case HousingStoreFront::class:
                /* @var HousingStoreFront $storeFront */
                $item = new HousingItem();
                $item->setDuration($data["duration"]);
                break;
            case TicketingStoreFront::class:
                /* @var TicketingStoreFront $storeFront */
                $item = new TicketingItem();
                $item->setValidTill(\DateTimeImmutable::createFromFormat("Y-m-d", $data["validTill"]));
                break;
            default:
                throw new \Exception("Unsupported Module");
                break;
        }
        return $item;
    }
}