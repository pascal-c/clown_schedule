<?php

namespace App\Form;

use App\Entity\ClownAvailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ClownAvailabilityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('wished_plays_month', ChoiceType::class, [
                'choices'  => range(0, 20),
                'label' => 'Wunsch Anzahl Spiele pro Monat',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('max_plays_month', ChoiceType::class, [
                'choices'  => range(0, 20),
                'label' => 'Maximale Anzahl Spiele pro Monat',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('max_plays_day', ChoiceType::class, [
                'choices'  => array(1 => 1, 2 => 2),
                'label' => 'Maximale Anzahl Spiele pro Tag',
                'expanded' => false,
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClownAvailability::class,
        ]);
    }
}
