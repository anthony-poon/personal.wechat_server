<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 9:57 AM
 */

namespace App\Controller\Core;

use App\Entity\Core\Location;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LocationAPIController extends Controller {
    /**
     * @Route("/api/cities", methods={"GET"})
     */
    public function getLocations() {
        $repo = $this->getDoctrine()->getRepository(Location::class);
        $locations = $repo->findAll();
        $rtn = [];
        foreach ($locations as $location) {
            /* @var \App\Entity\Core\Location $location */
            $rtn[] = [
                "id" => $location->getId(),
                "shortString" => $location->getShortString(),
                "name" => $location->getName()
            ];
        }
        return new JsonResponse([
            "status" => "success",
            "cities" => $rtn
        ]);
    }

    /**
     * @Route("/api/cities/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getLocation(string $id) {
        $repo = $this->getDoctrine()->getRepository(Location::class);
        preg_match("/^\D*0*(\d+)$$/", $id, $match);
        $id = $match[1];
        $location = $repo->find($id);
        /* @var \App\Entity\Core\Location $location */
        $rtn = [];
        foreach ($location->getModules() as $module) {
            /* @var \App\Entity\Core\AbstractModule $module */
            $rtn[] = [
                "id" => $module->getId(),
                "location" => $location->getName(),
                "name" => $module->getName(),
                "img"=> "/images/nothing.png"
            ];
        }
        return new JsonResponse($rtn);
    }
}