<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 30/11/2018
 * Time: 6:17 PM
 */

namespace App\FormType\Form\Core;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GlobalValueForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("visitorCount", NumberType::class, [
                "disabled" => true
            ])
            ->add("visitorCountMod", NumberType::class)
            ->add("globalNotification", TextareaType::class, [
                "required" => false
            ])
            ->add("moduleNotification", TextareaType::class, [
                "required" => false
            ])
            ->add("storeFrontNotification", TextareaType::class, [
                "required" => false
            ])
            ->add("storeItemNotification", TextareaType::class, [
                "required" => false
            ])
            ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
    }


}