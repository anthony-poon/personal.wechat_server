<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 23/11/2018
 * Time: 11:40 AM
 */

namespace App\FormType\Form\Core;

use App\Entity\Core\Ticketing\TicketingItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketingItemForm extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, [
                "label" => "Name"
            ])
            ->add("description", TextType::class, [
                "required" => false,
                "label" => "Description"
            ])
            ->add("price", NumberType::class, [
                "label" => "Price"
            ])
            ->add("Currency", ChoiceType::class, [
                "choices" => [
                    "GBP" => "GBP",
                    "RMB" => "RMB"
                ],
            ])
            ->add("weChatId", TextType::class, [
                "required" => false,
                "label" => "WeChat Id"
            ])
            ->add("validTill", DateType::class, [
                "widget" => 'single_text',
                'input' => 'datetime_immutable',
                "label" => "Effective Date"
            ])
            ->add("visitorCount", NumberType::class, [
                "disabled" => true,
                "label" => "Real Visitor Count"
            ])
            ->add("visitorCountModification", NumberType::class, [
                "label" => "Visitor Count Modification"
            ])
            ->add("isDisabled", CheckboxType::class, [
                "required" => false,
                "label" => "Disabled"
            ])
            ->add("isTraded", CheckboxType::class, [
                "required" => false,
                "label" => "Traded"
            ])
            ->add("lastTopTime", DateTimeType::class, [
                "disabled" => true,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => "AutoTop Timestamp (Used for item ordering)"
            ])
            ->add("setAutoTop", CheckboxType::class, [
                "required" => false,
                "mapped" => false,
                "label" => "Set AutoTop"
            ])
            ->add("createDate", DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                "label" => "Create Date"
            ])
            ->add("Submit", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            "data_class" => TicketingItem::class
        ]);
    }

}