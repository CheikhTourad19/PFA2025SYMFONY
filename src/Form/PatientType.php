<?php

namespace App\Form;
use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class)
            ->add('cin', TextType::class);
    }
    public function configureOptions(OptionsResolver $resolver): void{
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}