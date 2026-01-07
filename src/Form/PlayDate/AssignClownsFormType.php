<?php

namespace App\Form\PlayDate;

use App\Entity\Clown;
use App\Entity\PlayDate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignClownsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $playDate = $options['data'];

        $builder
            ->add('playingClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => fn (Clown $clown) => $clown->getLabelFor($playDate),
                'required' => false,
                'label' => 'Clowns',
                'expanded' => true,
                'multiple' => true,
                'help' => 'Der Wert in Klammern hinter dem Clown-Namen gibt die Präferenz des Clowns für den Spielort an.',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Kommentar',
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Zuordnung speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
