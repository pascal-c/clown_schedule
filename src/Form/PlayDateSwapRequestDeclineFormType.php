<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class PlayDateSwapRequestDeclineFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Hier wäre noch Platz für eine persönliche Nachricht an die anfragende Person:',
                'required' => false,
                'attr' => ['placeholder' => 'optional'],
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'Tauschanfrage ablehnen!',
                'attr' => [
                    'class' => 'btn-danger',
                ],
            ])
        ;
    }
}
