<?php

namespace App\Controller\Base;

use App\Entity\Base\Asset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController {

    /**
     * @Route("/api/assets/{id}", name="api_asset_get_item", methods={"GET"})
     */
    public function getItem($id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(Asset::class);
        $asset = $repo->find($id);
        if ($asset) {
            $file = base64_decode($asset->getBase64());
            $rsp = new Response();
            $rsp->headers->set("Content-Type", $asset->getMimeType());
            $rsp->headers->set("Content-Disposition", "inline");
            $rsp->setContent($file);
            return $rsp;
        } else {
            throw new NotFoundHttpException("Unable to locate entity");
        }
    }
}
