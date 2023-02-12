<?php

namespace App\Form;

use App\Entity\PlayDate;
use App\Entity\Venue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SpecialPlayDateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titel'])
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
            ->add('isSpecial', HiddenType::class, ['attr' => ['value' => '1']])
            ->add('save', SubmitType::class, ['label' => 'Sondertermin speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}