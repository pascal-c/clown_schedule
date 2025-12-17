<?php

namespace App\Form\PlayDate;

use App\Entity\RecurringDate;
use App\Entity\Venue;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecurringDateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Spielort',
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start',
                'input' => 'datetime_immutable',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Ende',
                'input' => 'datetime_immutable',
            ])
            ->add('rhythm', ChoiceType::class, [
                'choices' => [
                    'wöchentlich' => 'weekly',
                    'monatlich' => 'monthly',
                ],
                'label' => 'Rhythmus',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('dayOfWeek', ChoiceType::class, [
                'choices' => [
                    'Montag' => 'Monday',
                    'Dienstag' => 'Tuesday',
                    'Mittwoch' => 'Wednesday',
                    'Donnerstag' => 'Thursday',
                    'Freitag' => 'Friday',
                    'Samstag' => 'Saturday',
                    'Sonntag' => 'Sunday',
                ],
                'label' => 'Wochentag',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('every', ChoiceType::class, [
                'choices' => [
                    '1.' => 1,
                    '2.' => 2,
                    '3.' => 3,
                    '4.' => 4,
                    '5.' => 5,
                ],
                'label' => 'Alle wieviel Wochen/jeden wievielten im Monat',
                'expanded' => false,
                'multiple' => false,
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
            ->add('meetingTime', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Treffen',
                'minutes' => [0, 15, 30, 45],
            ])
            ->add('playTimeFrom', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
                'minutes' => [0, 15, 30, 45],
            ])
            ->add('playTimeTo', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
                'minutes' => [0, 15, 30, 45],
            ])
            ->add('isSuper', CheckboxType::class, [
                'label' => 'ist ein Super-Spieltermin? (nur relevant für Statistik)',
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Wiederkehrenden Termin anlegen'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecurringDate::class,
        ]);
    }
}
