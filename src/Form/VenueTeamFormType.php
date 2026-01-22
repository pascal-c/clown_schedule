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

class VenueTeamFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamActive', CheckboxType::class, [
                'label' => 'Clownsteam für diesen Spielort aktivieren',
                'required' => false,
                'help' => 'Dieser Spielort wird bevorzugt von Clowns aus diesem Team übernommen. Dies wird auch bei der Berechnung berücksichtigt.',
                'label_attr' => ['class' => 'checkbox-switch'],
                'attr' => [
                    'data-action' => 'toggle-by-checkbox#toggleVisibility',
                    'data-target' => 'toggle-by-checkbox.input',
                ],
            ])
            ->add('team', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Clownsteam',
                'help' => 'Dieser Spielort wird bevorzugt von Clowns aus diesem Team übernommen.',
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
