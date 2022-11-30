<?php

namespace App\Form;

use App\Entity\Clown;
use App\Entity\Venue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class VenueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('emails', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => EmailType::class,
                'label' => 'Kontakte',
                'entry_options' => [
                    'attr' => ['class' => 'email-box'],
                    'label' => 'Email',
                    'required' => false,
                ],
            ])
            ->add('responsibleClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Verantwortliche Clowns',
                'multiple' => true,
                'expanded' => true,
                ])
            ->add('daytimeDefault', ChoiceType::class, [
                'choices'  => [
                    'vormittags' => 'am',
                    'nachmittags' => 'pm',
                ],
                'label' => 'Standard Tageszeit für Spieltermine',
                'expanded' => true,
                'multiple' => false,
                ])
            ->add('meetingTime', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Treffen',
                ])   
            ->add('playTimeFrom', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (von)',
            ])    
            ->add('playTimeTo', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Spielzeit (bis)',
            ])  
            ->add('save', SubmitType::class, ['label' => 'Spielort speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
