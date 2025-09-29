<?php

namespace App\Form;

use App\Entity\Schedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleCalculateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keep_existing_assignments', CheckboxType::class, [
                'label' => 'bereits vorgenommene Clowns-Zuordnungen beibehalten',
                'mapped' => false,
                'required' => false,
            ])
            ->add('calculate_complex', SubmitType::class, [
                'label' => 'Spielplan jetzt berechnen',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
        ]);
    }
}
