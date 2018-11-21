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
use App\Entity\Core\AbstractStoreItem;;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            $arr["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $arr["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
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
            foreach (array_keys($arr["assets"]) as $key) {
                $arr["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $arr["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
            }
            return $arr;
        })->toArray();
        usort($storeItems, function($arr1, $arr2) {
            return ($arr1["createDate"] > $arr2["createDate"]) ? -1 : 1;
        });
        return new JsonResponse($storeItems);
    }
}