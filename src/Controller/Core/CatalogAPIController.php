<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 8:34 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\Catalog;
use App\Entity\Core\CatalogItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CatalogAPIController extends Controller {
    /**
     * @Route("/api/catalogs", methods={"GET"})
     */
    public function getCatalog() {
        /* @var $catalog \App\Entity\Core\Catalog */
        $catalogs = $this->getDoctrine()->getRepository(Catalog::class)->findAll();
        if ($catalogs) {
            $rtn = [];
            foreach ($catalogs as $catalog) {
                $rtn[] = [
                    "id" => $catalog->getId(),
                    "shortString" => $catalog->getShortString(),
                    "friendlyName" => $catalog->getFriendlyName(),
                ];
            }
            return new JsonResponse([
                "status" => "success",
                "catalogs" => $rtn
            ]);
        }
        throw new NotFoundHttpException("Entity cannot be found");
    }

    /**
     * @Route("/api/catalogs/{id}", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function getCatalogById(int $id) {
        /* @var $catalog \App\Entity\Core\Catalog */
        $catalog = $this->getDoctrine()->getRepository(Catalog::class)->find($id);
        if ($catalog) {
            return new JsonResponse([
                "status" => "success",
                "catalog" => [
                    "id" => $catalog->getId(),
                    "shortString" => $catalog->getShortString(),
                    "friendlyName" => $catalog->getFriendlyName(),
                ]
            ]);
        }
        throw new NotFoundHttpException("Entity cannot be found");
    }

    /**
     * @Route("/api/catalogs/{id}/catalog-items", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function getCatalogItems(int $id, Request $request) {
        /* @var $catalog \App\Entity\Core\Catalog */
        $region = $request->query->get("region") ?? null;
        $repo = $this->getDoctrine()->getRepository(CatalogItem::class);
        $query = [
            "catalog" => $id,
        ];
        if ($region) {
            $query["region"] = $region;
        }
        $catalogItems = $repo->findBy($query);
        if ($catalogItems) {
            $items = [];
            foreach ($catalogItems as $catalogItem) {
                /* @var $catalogItem \App\Entity\Core\CatalogItem */
                $urls = [];
                foreach ($catalogItem->getAssets() as $asset) {
                    /* @var $asset \App\Entity\Base\Asset */
                    $urls[] = $this->generateUrl("api_asset_get_item", [
                        "id" => $asset->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $items[] = [
                    "id" => $catalogItem->getId(),
                    "name" => $catalogItem->getName(),
                    "region" => $catalogItem->getRegion(),
                    "assets" => $urls
                ];
            }

            return new JsonResponse([
                "status" => "success",
                "catalogItems" => $items
            ]);
        }
        throw new NotFoundHttpException("Entity cannot be found");
    }
}