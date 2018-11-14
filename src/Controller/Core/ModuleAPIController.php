<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 2:38 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class ModuleAPIController extends Controller {
    /**
     * @Route("/api/modules", methods={"GET"})
     */
    public function getModules() {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $modules = $repo->findAll();
        $rtn = [];
        foreach ($modules as $module) {
            /* @var \App\Entity\Core\AbstractModule $module */
            $rtn[] = [
                "id" => $module->getId(),
                "name" => $module->getName(),

            ];
        }
        return new JsonResponse([
            "status" => "success",
            "modules" => $rtn
        ]);
    }

    /**
     * @Route("/api/modules/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getModule(int $id) {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $module = $repo->find($id);
        /* @var \App\Entity\Core\AbstractModule $module */
        $rtn = [
            "status" => "success",
            "id" => $module->getId(),
            "name" => $module->getName(),
            "storeFronts" => []
        ];
        foreach ($module->getStoreFronts() as $storeFront) {
            /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
            $rtn["storeFronts"][] = [
                "id" => $storeFront->getId(),
                "name" => $storeFront->getName(),
            ];
        }
        return new JsonResponse($rtn);
    }
}