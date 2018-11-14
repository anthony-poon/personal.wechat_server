<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 3:10 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
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
            $assets = [];
            foreach ($storeItem->getAssets() as $asset) {
                /* @var \App\Entity\Base\Asset $asset */
                $assets[] = $this->generateUrl("api_asset_get_item", [
                    "id" => $asset->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $rtn["storeItems"][] = [
                "id" => $storeItem->getId(),
                "name" => $storeItem->getName(),
                "description" => $storeItem->getDescription(),
                "assets" => $assets
            ];
        }
        return new JsonResponse($rtn);
    }

}