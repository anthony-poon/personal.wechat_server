<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 2:38 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
                "id" => $module->getPaddedId(),
                "name" => $module->getName(),

            ];
        }
        return new JsonResponse([
            "status" => "success",
            "modules" => $rtn
        ]);
    }

    /**
     * @Route("/api/modules/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getModule(string $id, ParameterBagInterface $bag) {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        preg_match("/^\D*0*(\d+)$$/", $id, $match);
        $id = $match[1];
        $module = $repo->find($id);
        /* @var AbstractModule $module */
        $rtn = [
            "id" => $module->getPaddedId(),
            "status" => "success",
            "id" => $module->getId(),
            "name" => $module->getName(),
            "storeFronts" => []
        ];
        foreach ($module->getStoreFronts() as $storeFront) {
            /* @var \App\Entity\Core\AbstractStoreFront $storeFront */
            $rtn["storeFronts"][] = [
                "id" => $storeFront->getPaddedId(),
                "name" => $storeFront->getName(),
            ];
        }
        return new JsonResponse($rtn);
    }
}