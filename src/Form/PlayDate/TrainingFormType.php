<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titel'])
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
            ->add('meetingTime', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Treffen',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
            ])
            ->add('playTimeFrom', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
            ])
            ->add('playTimeTo', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
            ])->add(
                $builder
                    ->create('type', HiddenType::class)
                    ->addModelTransformer(new CallbackTransformer(
                        fn (PlayDateType $type): string => $type->value,
                        fn (string $type): PlayDateType => PlayDateType::from($type),
                    ))
            )
            ->add('save', SubmitType::class, ['label' => 'Trainingstermin speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
