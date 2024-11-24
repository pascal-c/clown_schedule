<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegularPlayDateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datum',
                'input' => 'datetime_immutable',
            ])
            ->add('daytime', ChoiceType::class, [
                'choices' => [
                    'vormittags' => TimeSlotPeriodInterface::AM,
                    'nachmittags' => TimeSlotPeriodInterface::PM,
                    'ganztags' => TimeSlotPeriodInterface::ALL,
                ],
                'label' => 'Tageszeit',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Spielort',
            ])
            ->add('isSuper', CheckboxType::class, [
                'label' => 'ist ein Super-Spieltermin? (nur relevant für Statistik)',
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Spieltermin speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
