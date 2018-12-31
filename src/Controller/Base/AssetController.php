<?php

namespace App\Controller\Base;

use App\Entity\Base\Asset;
use App\Entity\Core\StoreItemAsset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController {

    /**
     * @Route("/api/assets/{id}", name="api_asset_get_item", methods={"GET"})
     */
    public function getItem($id, ParameterBagInterface $bag, Request $request) {
        $repo = $this->getDoctrine()->getRepository(Asset::class);
        $asset = $repo->find($id);
        $isFull = $request->get("full");
        if ($asset) {
            if (!$asset instanceof StoreItemAsset || $isFull) {
                $file = realpath($bag->get("upload_img_path")."/".$asset->getImgPath());
            } else {
                $file = realpath($bag->get("upload_img_path")."/".$asset->getThumbnailPath());
            }
            $rsp = new BinaryFileResponse($file);
            return $rsp;
        } else {
            throw new NotFoundHttpException("Unable to locate entity");
        }
    }
}
