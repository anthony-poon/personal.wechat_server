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
use App\Entity\Core\SecondHand\SecondHandItem;
use App\Entity\Core\SecondHand\SecondHandModule;
use App\Entity\Core\SecondHand\SecondHandStoreFront;
use App\Exception\ValidationException;
use App\Service\JsonValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;

class PersonalAPIController extends Controller {
    /**
     * @Route("/api/personal", methods={"GET"})
     */
    public function getPersonal() {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
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
                "location" => $store->getModule()->getLocation()->getName(),
                "locationId" => $store->getModule()->getLocation()->getId(),
            ];
        }
        return new JsonResponse($rtn);
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
        $em = $this->getDoctrine()->getManager();
        $em->persist($storeFront);
        $em->flush();
    }


    /**
     * @Route("/api/personal/store-fronts/{id}/store-items", methods={"POST"}, requirements={"id"="\d+"})
     * @throws \Exception
     * @throws ValidationException
     */
    public function createStoreItem(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(AbstractStoreFront::class);
        $storeFront = $repo->find($id);
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY", $storeFront);
        $user = $this->getUser();
        switch ($request->getContentType()) {
            case "json":
                switch (get_class($storeFront)) {
                    case SecondHandStoreFront::class:
                        /* @var \App\Entity\Core\SecondHand\SecondHandStoreFront $storeFront */
                        $item = new SecondHandItem();
                        $item->setStoreFront($storeFront);
                        break;
                    case HousingStoreFront::class:
                        /* @var \App\Entity\Core\Housingcb3c\HousingStoreFront $storeFront */
                        break;
                    default:
                        throw new \Exception("Unsupported Module");
                        break;
                }
                break;
            default:
                var_dump($request->getContentType());
                var_dump($request->request->all());
                var_dump($request->files->all());
                break;
        }
    }
}