<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 2:38 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
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
use App\Voter\StoreFrontVoter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    public function getModule(int $id, ParameterBagInterface $bag) {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $module = $repo->find($id);
        /* @var AbstractModule $module */
        $rtn = $module->getStoreFronts()->map(function(AbstractStoreFront $storeFront) {
            return $storeFront->jsonSerialize();
        })->toArray();
        foreach (array_keys($rtn) as $key) {
            if ($rtn[$key]["asset"]) {
                $rtn[$key]["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $rtn[$key]["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }
        usort($rtn, function($arr1, $arr2) {
            if ($arr1["isPremium"] xor $arr2["isPremium"]) {
                return -($arr1["isPremium"] <=> $arr2["isPremium"]);
            }
            return -($arr1["createDate"] <=> $arr2["createDate"]);
        });
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/modules/{id}/store-items", methods={"POST"}, requirements={"id"="[\w_]+"})
     */
    public function createStoreItem(int $id, Request $request) {
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