<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigCalculationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Config $config */
        $config = $options['data'];

        $builder
            ->add('useCalculation', CheckboxType::class, [
                'required' => false,
                'label' => 'Automatische Spielplanberechnung',
                'label_attr' => ['class' => 'checkbox-switch'],
                'help' => 'Wenn aktiviert, kann der Spielplan automatisch unter Berücksichtigung der Clownswünsche berechnet werden',
            ]);

        if ($config->useCalculation()) {
            $builder
                ->add('featureMaxPerWeekActive', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Feature “Max. Spielanzahl pro Woche”',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'help' => 'Clowns können sich eine maximale Spielanzahl pro Woche wünschen. Dies wird bei der Berechnung berücksichtigt.',
                ])
                ->add('featureAssignResponsibleClownAsFirstClownActive', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Feature “Verantwortliche Clowns“',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'help' => 'Es können jedem Spielort ein oder mehrere verantwortliche Clowns zugeordnet werden. Bei der Berechnung wird versucht als ersten Clown immer einen verantwortlichen Clown zu benennen. Gibt es mehrere verantwortliche Clowns pro Spielort, werden diese abwechselnd als 1. Clown zugeordnet. Ist kein verantwortlicher Clown verfügbar, wird ein Clown zugeordnet, der zuletzt dort spielte.',
                ])
                ->add('pointsPerMissingPerson', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro fehlender Zuordnung',
                ])
                ->add('pointsPerMaybePerson', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro zugeordnetem Clown, der nur kann, wenns sein muss',
                ])
                ->add('pointsPerTargetShifts', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spiel, das ein Clown zuviel oder zuwenig bekommt',
                ])
                ->add('pointsPerMaxPerWeek', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spiel, durch das ein Maximum pro Woche überschritten wird',
                ])
                ->add('featureClownVenuePreferencesActive', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Feature “Spielortpräferenzen der Clowns”',
                    'help' => 'Clowns können Spielortpräferenzen angeben. Dies wird bei der Berechnung berücksichtigt.',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'attr' => [
                        'data-action' => 'toggle-by-checkbox#toggleVisibility',
                        'data-target' => 'toggle-by-checkbox.input',
                    ],
                ])
                ->add('pointsPerPreferenceWorst', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "wenn\'s gar nicht anders geht"',
                    'row_attr' => [
                        'data-target' => 'toggle-by-checkbox.hideme',
                    ],
                ])
                ->add('pointsPerPreferenceWorse', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "na gut"',
                    'row_attr' => [
                        'data-target' => 'toggle-by-checkbox.hideme',
                    ],
                ])
                ->add('pointsPerPreferenceOk', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "ok"',
                    'row_attr' => [
                        'data-target' => 'toggle-by-checkbox.hideme',
                    ],
                ])
                ->add('pointsPerPreferenceBetter', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "sehr gerne"',
                    'row_attr' => [
                        'data-target' => 'toggle-by-checkbox.hideme',
                    ],
                ])
                ->add('pointsPerPreferenceBest', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "au ja, unbedingt!"',
                    'row_attr' => [
                        'data-target' => 'toggle-by-checkbox.hideme',
                    ],
                ]);
        }

        $builder
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
