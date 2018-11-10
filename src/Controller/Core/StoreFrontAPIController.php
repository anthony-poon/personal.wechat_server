<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 3:10 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractStoreFront;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
            "id" => $storeFront->getId(),
            "name" => $storeFront->getName(),
            "storeItems" => []
        ];
        foreach ($storeFront->getStoreItems() as $storeItem) {
            /* @var \App\Entity\Core\AbstractStoreItem $storeItem */
            $assets = [];
            foreach ($storeItem->getAssets() as $asset) {
                /* @var \App\Entity\Base\Asset $asset */
                $assets[] = [
                    "url" => $this->generateUrl("api_asset_get_item", [
                        "id" => $asset->getId()
                    ]),
                    "mimeType" => $asset->getMimeType()
                ];
            }
            $rtn["storeItems"][] = [
                "id" => $storeItem->getId(),
                "name" => $storeItem->getName(),
                "assets" => $assets
            ];
        }
        return new JsonResponse([
            "status" => "success",
            "storeFront" => $rtn
        ]);
    }
}