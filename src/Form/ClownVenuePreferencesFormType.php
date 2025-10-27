<?php

namespace App\Form;

use App\Entity\Clown;
use App\Value\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClownVenuePreferencesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Clown $clown */
        $clown = $builder->getData();
        foreach ($clown->getClownVenuePreferences() as $venuePreference) {
            $builder
                ->add('clownVenuePreferences'.$venuePreference->getId(), ChoiceType::class, [
                    'choices' => [
                        'nur wenn\'s gar nicht anders geht' => Preference::WORST->value,
                        'na gut' => Preference::WORSE->value,
                        'ok' => Preference::OK->value,
                        'sehr gerne' => Preference::BETTER->value,
                        'au ja, unbedingt!' => Preference::BEST->value,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'label_attr' => [
                        'class' => 'radio-inline',
                    ],
                    'label' => $venuePreference->getVenue()->getName(),
                    'mapped' => false,
                    'data' => $venuePreference->getPreference()->value,
                ]);
        }

        $builder->add('save', SubmitType::class, ['label' => 'speichern']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clown::class,
        ]);
    }
}
