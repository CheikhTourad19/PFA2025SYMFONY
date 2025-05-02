<?php
namespace App\Form;

use App\Entity\Task;
use App\Entity\Medecin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la tâche',
                'attr' => ['placeholder' => 'Entrez le titre de la tâche']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez la tâche en détail...'
                ]
            ])
            ->add('assignedTo', EntityType::class, [
                'class' => Medecin::class,
                'label' => 'Assigner à',
                'choice_label' => 'user.fullName',
                'attr' => ['class' => 'form-select']
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Échéance',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => ['class' => 'datepicker']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}