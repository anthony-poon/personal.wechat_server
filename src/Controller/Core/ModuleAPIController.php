<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 10/11/2018
 * Time: 2:38 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ModuleAPIController extends Controller {
    /**
     * @Route("/api/modules", methods={"GET"})
     */
    public function getModules() {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $modules = $repo->findAll();
        return new JsonResponse($modules);
    }

    /**
     * @Route("/api/modules/{id}", methods={"GET"}, requirements={"id"="[\w_]+"})
     */
    public function getModule(int $id, ParameterBagInterface $bag) {
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        $module = $repo->find($id);
        /* @var AbstractModule $module */
        $rtn = $module->getStoreFronts()->map(function(AbstractStoreFront $storeFront) {
            return $storeFront->jsonSerialize();
        })->toArray();
        foreach (array_keys($rtn) as $key) {
            $rtn[$key]["asset"] = $this->generateUrl("api_asset_get_item", ["id" => $rtn[$key]["asset"]],UrlGeneratorInterface::ABSOLUTE_URL);
        }
        return new JsonResponse($rtn);
    }
}