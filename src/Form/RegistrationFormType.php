<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'mapped' => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom', // ✅ Corrigé
                'mapped' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => true,
            ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro',
                'mapped' => true,
            ])
            ->add('cin', IntegerType::class, [
                'required' => false,
                'mapped' => false,
            ]);
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
