<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 30/11/2018
 * Time: 6:23 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\GlobalValue;
use App\FormType\Form\Core\GlobalValueForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GlobalValueController extends Controller {

    /**
     * @Route("/admin/global-value/edit", name="global_value_edit")
     */
    public function edit(Request $request) {
        $repo = $this->getDoctrine()->getRepository(GlobalValue::class);
        $globalValues = $repo->findAll();
        $data = [];
        $objCache = [];
        foreach ($globalValues as $value) {
            $data[$value->getKey()] = $value->getValue();
            $objCache[$value->getKey()] = $value;
        }
        $form = $this->createForm(GlobalValueForm::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            foreach ($data as $key => $value) {
                $objCache[$key]->setValue($value);
                $em->persist($objCache[$key]);
            }
            $em->flush();
            return $this->redirectToRoute("global_value_edit");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Edit Global Value",
            "form" => $form->createView(),
        ]);
    }
}