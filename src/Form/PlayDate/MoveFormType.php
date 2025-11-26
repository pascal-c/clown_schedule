<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MoveFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Neues Datum für den Spieltermin',
                'input' => 'datetime_immutable',
                'required' => true,
                'mapped' => false,
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
                'mapped' => false,
            ])
            ->add('meetingTime', TimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'choice',
                'label' => 'Treffen',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'mapped' => false,
            ])
            ->add('playTimeFrom', TimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'mapped' => false,
            ])
            ->add('playTimeTo', TimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'mapped' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Kommentar',
                'required' => false,
                'attr' => ['placeholder' => 'Optional: Grund der Verschiebung...'],
            ])
            ->setMethod('PUT')
            ->setAction($this->urlGenerator->generate('play_date_move', ['id' => $options['data']->getId()]))
            ->add('save', SubmitType::class, ['label' => 'Termin jetzt verschieben'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
