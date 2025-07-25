<?php

namespace App\Form;

use App\Entity\Clown;
use App\Entity\Venue;
use App\Repository\ConfigRepository;
use App\Value\TimeSlotPeriodInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenueFormType extends AbstractType
{
    public function __construct(private ConfigRepository $configRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Kurzname'])
            ->add('officialName', TextType::class, ['label' => 'Offizieller Name'])
            ->add('streetAndNumber', TextType::class, ['label' => 'Straße und Hausnummer', 'required' => false])
            ->add('postalCode', TextType::class, ['label' => 'PLZ', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ort', 'required' => false])
            ->add('responsibleClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Verantwortliche Clowns',
                'help' => 'Bei der Spielplanerstellung wird versucht, immer einen der verantwortlichen Clowns als ersten Clown zuzuordnen',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('blockedClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Gesperrte Clowns',
                'help' => 'Bei der Spielplanerstellung wird ein gesperrter Clown niemals diesem Spielort zugeordnet',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('daytimeDefault', ChoiceType::class, [
                'choices' => [
                    'vormittags' => TimeSlotPeriodInterface::AM,
                    'nachmittags' => TimeSlotPeriodInterface::PM,
                    'ganztags' => TimeSlotPeriodInterface::ALL,
                ],
                'label' => 'Standard Tageszeit für Spieltermine',
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
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Spielort speichern'])
        ;

        if (!$this->configRepository->isFeatureCalculationActive()) {
            $builder->remove('blockedClowns');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
