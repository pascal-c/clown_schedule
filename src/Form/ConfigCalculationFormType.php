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
                'label' => 'Automatische Berechnung',
                'help' => 'Wenn aktiviert, kann der Spielplan automatisch unter Berücksichtigung der Clownswünsche berechnet werden',
            ]);

        if ($config->useCalculation()) {
            $builder
                ->add('featureMaxPerWeekActive', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Feature “Max. Spielanzahl pro Woche”',
                    'help' => 'Clowns können sich eine maximale Spielanzahl pro Woche wünschen. Dies wird bei der Berechnung berücksichtigt.',
                ])
                ->add('featureAssignResponsibleClownAsFirstClownActive', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Verantwortlichen Clown als 1. Clown zuordnen',
                    'help' => 'Gibt es mehrere verantwortliche Clowns pro Spielort, werden diese abwechselnd als 1. Clown zugeordnet. Ist kein verantwortlicher Clown verfügbar, wird ein Clown zugeordnet, der zuletzt dort spielte',
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
                ->add('pointsPerPreferenceWorst', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "wenn\'s gar nicht anders geht"',
                ])
                ->add('pointsPerPreferenceWorse', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "na gut"',
                ])
                ->add('pointsPerPreferenceOk', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "ok"',
                ])
                ->add('pointsPerPreferenceBetter', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "sehr gerne"',
                ])
                ->add('pointsPerPreferenceBest', IntegerType::class, [
                    'required' => true,
                    'label' => 'Punkte pro Spielortpräferenz "au ja, unbedingt!"',
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
