<?php

namespace App\Form;

use App\Entity\Clown;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenueResponsibleClownsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignResponsibleClownAsFirstClown', CheckboxType::class, [
                'label' => '"Verantwortlichen Clown als ersten Clown" aktivieren',
                'required' => false,
                'help' => 'Bei der Berechnung wird versucht als ersten Clown immer einen verantwortlichen Clown zu benennen.',
                'label_attr' => ['class' => 'checkbox-switch'],
                'attr' => [
                    'data-action' => 'toggle-by-checkbox#toggleVisibility',
                    'data-target' => 'toggle-by-checkbox.input',
                ],
            ])
            ->add('responsibleClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Verantwortliche Clowns',
                'help' => 'Gibt es mehrere verantwortliche Clowns pro Spielort, werden diese abwechselnd als 1. Clown zugeordnet. Ist kein verantwortlicher Clown verfügbar, wird ein Clown zugeordnet, der zuletzt dort spielte.',
                'multiple' => true,
                'expanded' => true,
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
