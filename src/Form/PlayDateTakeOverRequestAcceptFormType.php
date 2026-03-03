<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PlayDateTakeOverRequestAcceptFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accept', SubmitType::class, [
                'label' => 'Diesen Spieltermin jetzt übernehmen',
                'attr' => [
                    'class' => 'btn-success',
                    'title' => 'Das wird schön!',
                ],
            ])
        ;
    }
}
