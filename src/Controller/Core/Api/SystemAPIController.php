<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 30/11/2018
 * Time: 11:32 PM
 */

namespace App\Controller\Core\Api;

use App\Entity\Core\GlobalValue;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SystemAPIController extends Controller {

    /**
     * @Route("/api/system/notifications")
     */
    public function getNotification() {
        $repo = $this->getDoctrine()->getRepository(GlobalValue::class);
        $keys = [
            "globalNotification",
            "moduleNotification",
            "storeFrontNotification",
            "storeItemNotification",
        ];
        $rtn = [];
        foreach ($keys as $key) {
            $gv = $repo->findOneBy([
                "key" => $key
            ]);
            $rtn[$key] = $gv->getValue();
        }
        $visitorCount = $repo->findOneBy([
            "key" => "visitorCount"
        ])->getValue() + $repo->findOneBy([
            "key" => "visitorCountMod"
        ])->getValue();
        $rtn["visitorCount"] = $visitorCount;
        return new JsonResponse($rtn);
    }
}