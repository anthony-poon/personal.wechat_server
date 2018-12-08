<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 30/11/2018
 * Time: 6:17 PM
 */

namespace App\FormType\Form\Core;

use App\Entity\Core\WeChatUser;
use App\FormType\Component\CompositeCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GlobalValueForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add("visitorCount", NumberType::class, [
                "disabled" => true
            ])
            ->add("visitorCountMod", NumberType::class)
            ->add("globalNotification", CompositeCollectionType::class, [
                "entry_type" => TextareaType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "force_serial_index" => true
            ])
            ->add("moduleNotification", CompositeCollectionType::class, [
                "entry_type" => TextareaType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "force_serial_index" => true
            ])
            ->add("storeFrontNotification", CompositeCollectionType::class, [
                "entry_type" => TextareaType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "force_serial_index" => true
            ])
            ->add("storeItemNotification", CompositeCollectionType::class, [
                "entry_type" => TextareaType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "force_serial_index" => true
            ])
            ->add("mockLogin", TextType::class,[
                "required" => false,
                "label" => "Open Id for Mock Login"
            ])
            ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
    }


}