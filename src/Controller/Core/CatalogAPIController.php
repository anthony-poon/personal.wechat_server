<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 8:34 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\Catalog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CatalogAPIController extends Controller {
    /**
     * @Route("/api/catalogs/{id}", requirements={"id"="\d+"})
     */
    public function getCatalog($id) {
        /* @var $catalog \App\Entity\Core\Catalog */
        $catalog = $this->getDoctrine()->getRepository(Catalog::class)->find($id);
        if ($catalog) {
            return new JsonResponse([
                "status" => "success",
                "catalog" => [
                    "shortString" => $catalog->getShortString(),
                    "friendlyName" => $catalog->getFriendlyName(),
                    "catalogItems" => $catalog->getCatalogItems()->toArray()
                ]
            ]);
        }
        throw new NotFoundHttpException("Entity cannot be found");
    }

}