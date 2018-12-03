<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 2/12/2018
 * Time: 2:39 PM
 */

namespace App\FormType\Form\Core;

use App\Entity\Core\StickyTicket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StickyTicketForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("code", TextType::class, [
            "disabled" => true,
        ])->add("createDate", DateTimeType::class, [
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            "disabled" => true
        ])->add("expireDate", DateTimeType::class, [
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            "disabled" => true
        ])->add("isConsumed", CheckboxType::class, [
            "required" => false
        ])
        ->add("submit", SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => StickyTicket::class
        ]);
    }

}