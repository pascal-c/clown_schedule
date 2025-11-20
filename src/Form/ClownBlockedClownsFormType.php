<?php

namespace App\Form;

use App\Entity\Clown;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClownBlockedClownsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('blockedClowns', EntityType::class, [
                'class' => Clown::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Gesperrte Clowns',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clown::class,
        ]);
    }
}
