<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false, // Optional field
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false, // This field is not mapped to the entity
                'required' => false, // Optional unless changing password
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe actuel pour changer le mot de passe.',
                        'groups' => ['password_change'], // Validate only when changing password
                    ]),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false, // This field is not mapped to the entity
                'required' => false, // Optional unless changing password
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nouveau mot de passe.',
                        'groups' => ['password_change'], // Validate only when changing password
                    ]),

                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
