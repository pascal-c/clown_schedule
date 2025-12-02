<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use App\Guard\PlayDateGuard;
use App\Value\PlayDateType;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialPlayDateFormType extends AbstractType
{
    public function __construct(private PlayDateGuard $playDateGuard)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var PlayDate $playDate */
        $playDate = $options['data'];
        $canEdit = $this->playDateGuard->canEdit($playDate);
        $helpText = !$canEdit ? 'Achtung! Der Spieltermin liegt in der Vergangenheit bzw. die Spielplanerstellung ist schon abgeschlossen!' : '';

        $builder
            ->add('title', TextType::class, ['label' => 'Titel'])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datum',
                'input' => 'datetime_immutable',
                'help' => $helpText,
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
                'help' => $helpText,
            ])
            ->add('meetingTime', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Treffen',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'help' => $helpText,
            ])
            ->add('playTimeFrom', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'help' => $helpText,
            ])
            ->add('playTimeTo', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
                'required' => false,
                'minutes' => [0, 15, 30, 45],
                'help' => $helpText,
            ])
            ->add('isSuper', CheckboxType::class, [
                'label' => 'ist ein Super-Spieltermin? (nur relevant für Statistik)',
                'required' => false,
            ])
            ->add(
                $builder
                    ->create('type', HiddenType::class)
                    ->addModelTransformer(new CallbackTransformer(
                        fn (PlayDateType $type): string => $type->value,
                        fn (string $type): PlayDateType => PlayDateType::from($type),
                    ))
            )
            ->add('save', SubmitType::class, ['label' => 'Zusatztermin speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
