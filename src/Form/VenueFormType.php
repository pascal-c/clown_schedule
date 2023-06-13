<?php

namespace App\Form;

use App\Entity\Clown;
use App\Entity\Venue;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class VenueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('streetAndNumber', TextType::class, ['label' => 'Straße und Hausnummer', 'required' => false])
            ->add('postalCode', TextType::class, ['label' => 'PLZ', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ort', 'required' => false])
            ->add('contactPerson', TextType::class, ['label' => 'Ansprechperson', 'required' => false])
            ->add('contactPhone', TelType::class, ['label' => 'Telefon', 'required' => false])
            ->add('contactEmail', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('feeByCar', MoneyType::class, ['label' => 'Honorar PKW', 'scale' => 0, 'html5' => true, 'required' => false])
            ->add('feeByPublicTransport', MoneyType::class, ['label' => 'Honorar Öffis', 'scale' => 0, 'html5' => true, 'required' => false])
            ->add('kilometers', NumberType::class, ['label' => 'Kilometer', 'html5' => true, 'required' => false])
            ->add('kilometersFeeForAllClowns', CheckboxType::class, ['label' => 'Kilometergeld für beide Clowns', 'required' => false])
            ->add('responsibleClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Verantwortliche Clowns',
                'multiple' => true,
                'expanded' => true,
                ])
            ->add('daytimeDefault', ChoiceType::class, [
                'choices'  => [
                    'vormittags' => TimeSlotPeriodInterface::AM,
                    'nachmittags' => TimeSlotPeriodInterface::PM,
                    'ganztags'   => TimeSlotPeriodInterface::ALL,
                ],
                'label' => 'Standard Tageszeit für Spieltermine',
                'expanded' => true,
                'multiple' => false,
                ])
            ->add('meetingTime', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Treffen',
                ])   
            ->add('playTimeFrom', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
            ])    
            ->add('playTimeTo', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
            ])
            ->add('comments', TextareaType::class, [
                'label' => 'Bemerkungen',
                'required' => false,
                ])
            ->add('url', UrlType::class, [
                'label' => 'URL (für weitere Infos zur Einrichtung)',
                'required' => false,
                ])
            ->add('isSuper', CheckboxType::class, [
                'label' => 'ist ein Super-Spielort? (nur relevant für Statistik)',
                'required' => false
                ])
            ->add('save', SubmitType::class, ['label' => 'Spielort speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
