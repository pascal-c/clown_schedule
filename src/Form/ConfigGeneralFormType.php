<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigGeneralFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ])
            ->add('specialPlayDateUrl', UrlType::class, [
                'required' => false,
                'label' => 'Zusatztermine Link',
                'help' => 'Dieser Link wird in den Details aller Zusatztermine angezeigt',
            ])
            ->add('featurePlayDateChangeRequestsActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature "Spieltermine tauschen"',
                'help' => 'Clowns können untereinander Spieltermine tauschen oder abgeben.',
            ])
            ->add('featureTeamsActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature "Clownsteams"',
                'help' => 'Für Spielorte können Clownsteams definiert werden, die diesen Spielort bevorzugt übernehmen.',
                'attr' => [
                    'data-action' => 'toggle-by-checkbox#toggleVisibility',
                    'data-target' => 'toggle-by-checkbox.input',
                ],
            ])
            ->add('teamCanAssignPlayingClowns', CheckboxType::class, [
                'required' => false,
                'label' => 'Mitglieder von Clownsteams können Zuordnungen vornehmen',
                'help' => 'Die Zuordnung von Clowns zu Spielterminen kann von Mitgliedern des Teams dieses Spielortes vorgenommen werden. Sie bekommen auch einen Hinweis im Dashboard, wenn es offene Zuordnungen für diesen Spielort gibt.',
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('feeLabel', TextType::class, [
                'required' => true,
                'label' => 'Bezeichnung für Standard-Honorar',
            ])
            ->add('alternativeFeeLabel', TextType::class, [
                'required' => false,
                'label' => 'Bezeichnung für alternatives Honorar',
                'help' => 'Wenn angegeben, kann ein alternatives Honorar angegeben werden. Wenn leer gelassen, wird kein alternatives Honorar angezeigt.',
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
