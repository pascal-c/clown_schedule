<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class PlayDateChangeRequestCancelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Hier wäre noch Platz für eine persönliche Nachricht an die angefragte Person:',
                'required' => false,
                'attr' => ['placeholder' => 'optional'],
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'Anfrage abbrechen',
                'attr' => [
                    'class' => 'btn-danger',
                ],
            ])
        ;
    }
}
