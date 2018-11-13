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
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Service\JSONValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
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
        return new JsonResponse([
            "status" => "success",
            "module" => $rtn
        ]);
    }

    /**
     * @Route("/api/modules/{id}/store-fronts", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function createStoreFront(int $id, Request $request, JSONValidator $validator) {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        $repo = $this->getDoctrine()->getRepository(AbstractModule::class);
        /* @var \App\Entity\Core\AbstractModule $module */
        $module = $repo->find($id);
        $user = $this->getUser();
        if ($module->getStoreFronts()->exists(function(int $key, AbstractStoreFront $storeFront) use ($user){
            return $storeFront->getOwner() === $user;
        })) {
           throw new \Exception("Current user already have a store front in the module");
        }
        $json = json_decode($request->getContent(), true);
        switch (get_class($module)) {
            case SecondHandModule::class:
                $response = $validator->validate($json, [
                    "name" => new Assert\NotBlank()
                ]);

                $storeFront = new SecondHandStoreFront();
                $storeFront->setModule($module);
                $storeFront->setName($json["name"]);
                $storeFront->setOwner($user);
                break;
            case HousingModule::class:
                $validator->validate($json, [
                    "name" => new Assert\NotBlank()
                ]);
                $storeFront = new HousingStoreFront();
                $storeFront->setModule($module);
                $storeFront->setName($json["name"]);
                $storeFront->setOwner($user);
                break;
            default:
                throw new \Exception("Unsupported module type.");
                break;
        }
    }
}