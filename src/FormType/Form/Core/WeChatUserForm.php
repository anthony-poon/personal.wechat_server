<?php
namespace App\FormType\Form\Core;

use App\Entity\Core\WeChatUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class WeChatUserForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /* @var WeChatUser $user */
        $builder->add("username", TextType::class)
            ->add("fullName", TextType::class)
            ->add("weChatOpenId", TextType::class, [
                "disabled" => true
            ])
            ->add("isPremium", CheckboxType::class)
            ->add("submit", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => WeChatUser::class,
        ]);
    }
}