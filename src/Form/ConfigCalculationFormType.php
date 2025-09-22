<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
