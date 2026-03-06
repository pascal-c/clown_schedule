<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PlayDateTakeOverRequestCancelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accept', SubmitType::class, [
                'label' => 'Anfrage abbrechen',
                'attr' => [
                    'class' => 'btn-danger',
                ],
            ])
        ;
    }
}
