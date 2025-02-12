<?php

namespace App\Form;

use App\Entity\ClownAvailability;
use App\Repository\ConfigRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClownAvailabilityFormType extends AbstractType
{
    public function __construct(private ConfigRepository $configRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('wishedPlaysMonth', ChoiceType::class, [
                'choices' => range(0, 20),
                'label' => 'Gewünschte Anzahl Spiele pro Monat',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('maxPlaysMonth', ChoiceType::class, [
                'choices' => range(0, 20),
                'label' => 'Maximale Anzahl Spiele pro Monat',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('maxPlaysDay', ChoiceType::class, [
                'choices' => [1 => 1, 2 => 2],
                'label' => 'Maximale Anzahl Spiele pro Tag',
                'expanded' => false,
                'multiple' => false,
            ]);
        if ($this->configRepository->isFeatureMaxPerWeekActive()) {
            $builder->add('softMaxPlaysWeek', ChoiceType::class, [
                'choices' => range(0, 7),
                'label' => 'Gewünschte maximale Anzahl Spiele pro Woche',
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'help' => 'Achtung! Wenn Du diese Option nutzt, kann es passieren, dass Du weniger Spieltermine bekommst als gewünscht.',
            ]);
        }
        $builder
            ->add('additionalWishes', TextareaType::class, [
                'label' => 'Weitere Wünsche oder Anmerkungen',
                'required' => false,
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
