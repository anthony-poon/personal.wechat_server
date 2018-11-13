<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 13/11/2018
 * Time: 3:04 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\AbstractModule;
use App\Entity\Core\AbstractStoreFront;
use App\Entity\Core\Housing\HousingModule;
use App\Entity\Core\Housing\HousingStoreFront;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Exception\ValidationException;
use App\Service\JsonValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;

class PersonalAPIController extends Controller {
    /**
     * @var Route("/api/personal")
     */
    public function getPersonal() {
        /* @var \App\Entity\Base\User $user */
        $user = $this->getUser();
        $rtn = [
            "id" => $user->getId(),
            "openId" => $user->getWeChatOpenId(),
            "fullName" => $user->getFullName(),
            "stores" => []
        ];
        foreach ($user->getStores() as $store) {
            /* @var \App\Entity\Core\AbstractStoreFront $store */
            $rtn["stores"][] = [
                "id" => $store->getId(),
                "name" => $store->getName(),
                "moduleId" => $store->getModule()->getId(),
                "moduleName" => $store->getModule()->getName(),
                "location" => $store->getModule()->getLocation(),
            ];
        }
    }

    /**
     * @Route("/api/personal/modules/{id}/store-fronts", methods={"POST"}, requirements={"id"="\d+"})
     * @throws ValidationException
     * @throws \Exception
     */
    public function createStoreFront(int $id, Request $request, JsonValidator $validator) {
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
                $validator->validate($json, [
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