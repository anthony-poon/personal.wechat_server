<?php
namespace App\FormType;

use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EditUserFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /* @var User $user */
        $builder->add("username", TextType::class)
            ->add("fullName", TextType::class)
            ->add("plainPassword", RepeatedType::class, array(
                "type" => PasswordType::class,
                "required" => false,
                "invalid_message" => "Repeat password did not match.",
                "first_options"  => array(
                    "required" => false,
                    "label" => "Password"
                ),
                "second_options" => array(
                    "required" => false,
                    "label" => "Repeat Password"
                ),
            ))
            ->add('email', EmailType::class)
            ->add("roles", EntityType::class, array(
                "class" => UserRole::class,
                "data" => $builder->getData()->getRoles()[0],
                "choice_label" => function($role) {
                    switch ($role->getRole()) {
                        case "ROLE_ADMIN":
                            return "Administrator";
                        case "ROLE_USER":
                            return "User";
                        default:
                            return $role->getRole();
                    }
                }))
            ->add("submit", SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}