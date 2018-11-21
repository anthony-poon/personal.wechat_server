<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 20/11/2018
 * Time: 5:19 PM
 */

namespace App\Controller\Core;

use App\Entity\Base\Asset;
use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\AbstractStoreItem;
use App\Entity\Core\Housing\HousingItem;
use App\Entity\Core\PaddedId;
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\Ticketing\TicketingItem;
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
        $query = $request->query->all();
        if (array_key_exists("module", $query) && $query["module"]) {
            $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
            $id = (int) $query["module"];
            $module = $repo->find($id);
            if (!$module) {
                throw new NotFoundHttpException("Cannot find module");
            }
            $rtn = [];
            /* @var AbstractModule $module */
            foreach ($module->getStoreFronts() as $storeFront) {
                /* @var AbstractStoreFront $storeFront */
                foreach ($storeFront->getStoreItems() as $storeItem) {
                    /* @var AbstractStoreItem $storeItem */

                    $arr = $storeItem->jsonSerialize();
                    foreach (array_keys($arr["assets"]) as $key) {
                        $arr["assets"][$key] = $this->generateUrl("api_asset_get_item", ["id" => $arr["assets"][$key]],UrlGeneratorInterface::ABSOLUTE_URL);
                    }
                    $rtn[] = $arr;
                }
            }
            usort($rtn, function($arr1, $arr2) {
                return ($arr1["createDate"] > $arr2["createDate"]) ? -1 : 1;
            });
            return new JsonResponse($rtn);
        }
        throw new \Exception("Unsupported Methods");
    }
}