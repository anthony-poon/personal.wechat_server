<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 20/11/2018
 * Time: 5:19 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\StoreItemAsset;
use App\Entity\Core\Ticketing\TicketingItem;
use App\Voter\StoreItemVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StoreItemAPIController extends Controller{
    /**
     * @Route("/api/store-items", methods={"GET"})
     */
    public function getStoreItems(Request $request) {
        if ($request->query->get("module")) {
            $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
            $id = (int) $request->query->get("module");
            $userId = (int) $request->query->get("user");
            $module = $repo->find($id);
            if (!$module) {
                throw new NotFoundHttpException("Cannot find module");
            }
            $showTraded = $request->query->get("showTraded") == true;
            $showDisabled = $request->query->get("showDisabled") == true;
            $showExpired = $request->query->get("showExpired") == true;
            $rtn = [];
            /* @var AbstractModule $module */
            foreach ($module->getStoreFronts() as $storeFront) {
                /* @var AbstractStoreFront $storeFront */
                foreach ($storeFront->getStoreItems() as $storeItem) {
                    /* @var AbstractStoreItem $storeItem */
                    if (empty($userId) || ($userId && $userId == $storeItem->getStoreFront()->getOwner()->getId())) {
                        if ($storeItem->isActive($showTraded, $showDisabled, $showExpired)) {
                            $arr = $storeItem->jsonSerialize();
                            if ($arr["assets"]) {
                                foreach (array_keys($arr["assets"]) as $key) {
                                    $arr["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $arr["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
                                }
                            }
                            $rtn[] = $arr;
                        }
                    }
                }
            }
            usort($rtn, function($arr1, $arr2) {
                if ($arr1["isSticky"] xor $arr2["isSticky"]) {
                    return -($arr1["isSticky"] <=> $arr2["isSticky"]);
                }
                return -($arr1["createDate"] <=> $arr2["createDate"]);
            });
            return new JsonResponse($rtn);
        }
        throw new \Exception("Unsupported Methods");
    }

    /**
     * @Route("/api/store-items/{id}", methods={"GET"})
     */
    public function getStoreItem(int $id) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        $storeItem = $repo->find($id);
        if (empty($storeItem)) {
            throw new NotFoundHttpException("Entity not found");
        }
        $storeItem->setVisitorCount($storeItem->getVisitorCount() + 1);
        $rtn = $storeItem->jsonSerialize();
        foreach (array_keys($rtn["assets"]) as $key) {
            $rtn["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $rtn["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/store-items/{id}/assets", methods={"POST"}, name="api_store_item_create_asset")
     */
    public function createAssets(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
        $storeItem = $repo->find($id);
        if (empty($storeItem)) {
            throw new NotFoundHttpException("Cannot locate entity");
        }
        $this->denyAccessUnlessGranted(StoreItemVoter::UPDATE, $storeItem);
        /* @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = array_values($request->files->all())[0];
        $base64 = base64_encode(file_get_contents($file->getRealPath()));
        $asset = new StoreItemAsset();
        $asset->setNamespace(get_class($storeItem));
        $asset->setMimeType($file->getMimeType());
        $asset->setBase64($base64);
        $asset->setStoreItem($storeItem);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->persist($asset);
        $em->flush();
        return new JsonResponse($storeItem);
    }

    /**
     * @Route("/api/store-items/assets/{id}", methods={"DELETE"}, name="api_store_item_delete_asset")
     */
    public function deleteAsset(int $id) {
        $repo = $this->getDoctrine()->getRepository(StoreItemAsset::class);
        $asset = $repo->find($id);
        if (empty($asset)) {
            throw new NotFoundHttpException("Cannot locate Entity");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($asset);
        $em->flush();
        return new JsonResponse([
            "status" => "success"
        ]);
    }

    /**
     * @Route("/api/store-items/{id}", methods={"PUT"})
     */
    public function updateItem(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        $storeItem = $repo->find($id);
        if (empty($storeItem)) {
            throw new NotFoundHttpException("Cannot location entity");
        }
        $this->denyAccessUnlessGranted(StoreItemVoter::UPDATE, $storeItem);
        $json = json_decode($request->getContent(), true);
        $storeItem->jsonDeserialize($json);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->flush();
        return new JsonResponse($storeItem);
    }

    /**
     * @Route("/api/store-items/{id}", methods={"DELETE"})
     */
    public function deleteItem(int $id) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreItem::class);
        /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
        $storeItem = $repo->find($id);
        if (empty($storeItem)) {
            throw new NotFoundHttpException("Cannot locate entity");
        }
        $this->denyAccessUnlessGranted(StoreItemVoter::DELETE, $storeItem);
        $storeItem->setIsDisabled(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeItem);
        $em->flush();
        return new JsonResponse([
            "status" => "success"
        ]);
    }
}