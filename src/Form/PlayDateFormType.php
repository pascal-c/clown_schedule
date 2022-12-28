<?php

namespace App\Form;

use App\Entity\PlayDate;
use App\Entity\Venue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PlayDateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text', 
                'label' => 'Datum',
                'input' => 'datetime_immutable',
                ])
            ->add('daytime', ChoiceType::class, [
                'choices'  => [
                    'vormittags' => 'am',
                    'nachmittags' => 'pm',
                ],
                'label' => 'Tageszeit',
                'expanded' => true,
                'multiple' => false,
                ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Spielort',
                ])
            ->add('save', SubmitType::class, ['label' => 'Spieltermin speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
