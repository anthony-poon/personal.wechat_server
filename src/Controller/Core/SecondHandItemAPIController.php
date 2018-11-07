<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 7/11/2018
 * Time: 5:14 PM
 */

namespace App\Controller\Core;

use App\Entity\Base\Asset;
use App\Entity\Core\AbstractStoreItem;
use App\Entity\Core\SecondHandItem;
use App\Entity\Core\Store;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SecondHandItemAPIController extends Controller {
    /**
     * @Route("/api/second-hand-repo/stores", methods={"GET"})
     */
    public function getStores(Request $request) {
        $repo = $this->getDoctrine()->getRepository(SecondHandItem::class);
        $query = [];
        foreach ($request->query->all() as $k => $v) {
            switch ($k) {
                case "city";
                    $query[$k] = $v;
                    break;
            }
        }
        $items = $repo->findBy($query);
        // TODO: Optimised with query
        $stores = [];
        foreach ($items as $item) {
            /* @var SecondHandItem $item */
            $stores[$item->getStore()->getId()] = $item->getStore();
        }
        $rtn = [];
        foreach ($stores as $store) {
            /* @var \App\Entity\Core\Store $store */
            $rtn[] = [
                "id" => $store->getId(),
                "name" => $store->getName(),
                "owner" => $store->getOwner()->getFullName(),
            ];
        }
        return new JsonResponse([
            "status" => "success",
            "stores" => $rtn
        ]);
    }

    /**
     * @Route("/api/second-hand-repo/stores/{id}/store-items", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getItems(int $id) {
        $repo = $this->getDoctrine()->getRepository(Store::class);
        /* @var \App\Entity\Core\Store $store */
        $store = $repo->find($id);
        $items = $store->getStoreItems(SecondHandItem::class) ?? null;
        if ($items) {
            $rtn = [];
            foreach ($items as $item) {
                /* @var \App\Entity\Core\SecondHandItem $item */
                $rtn[] = [
                    "id" => $item->getId(),
                    "name" => $item->getName(),
                    "description" => $item->getDescription(),
                    "assets" => $item->getAssets()->map(function(Asset $asset) {
                        return $this->generateUrl("api_asset_get_item", [
                            "id" => $asset->getId()
                        ]);
                    })->toArray()
                ];
            }
            return new JsonResponse([
                "status" => "success",
                "storeItems" => $rtn
            ]);
        }
        throw new NotFoundHttpException("Entity not found.");
    }
}