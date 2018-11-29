<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 9:57 AM
 */

namespace App\Controller\Core\Api;

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
        return new JsonResponse($locations);
    }

    /**
     * @Route("/api/cities/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getLocation(int $id) {
        $repo = $this->getDoctrine()->getRepository(Location::class);
        $location = $repo->find($id);
        /* @var \App\Entity\Core\Location $location */
        return new JsonResponse($location->getModules()->toArray());
    }
}