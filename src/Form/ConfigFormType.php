<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('specialPlayDateUrl', UrlType::class, [
                'required' => false,
                'label' => 'Zusatztermine Link',
                'help' => 'Dieser Link wird in den Details aller Zusatztermine angezeigt',
            ])
            ->add('featureMaxPerWeekActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature “Max. Spielanzahl pro Woche”',
                'help' => 'Wenn aktiviert, können Clowns zu ihren Verfügbarkeiten eine maximale Spielanzahl pro Woche angeben',
            ])
            ->add('federalState', ChoiceType::class, [
                'choices' => [
                    '-- kein Bundesland -- nur bundesweite Feiertage anzeigen' => null,
                    'Baden-Württemberg' => 'BW',
                    'Bayern' => 'BY',
                    'Berlin' => 'BE',
                    'Brandenburg' => 'BB',
                    'Bremen' => 'HB',
                    'Hamburg' => 'HH',
                    'Hessen' => 'HE',
                    'Mecklenburg-Vorpommern' => 'MV',
                    'Niedersachsen' => 'NI',
                    'Nordrhein-Westfalen' => 'NW',
                    'Rheinland-Pfalz' => 'RP',
                    'Saarland' => 'SL',
                    'Sachsen' => 'SN',
                    'Sachsen-Anhalt' => 'ST',
                    'Schleswig-Holstein' => 'SH',
                    'Thüringen' => 'TH',
                ],
                'label' => 'Bundesland',
                'help' => 'für die Anzeige von Ferien und Feiertagen im Kalender',
                'required' => true,
                'empty_data' => 'John Doe',
            ])
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
