<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PlayDateGiveOffRequestAcceptFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Hier kannst Du noch eine persönliche Nachricht an die anfragende Person hinterlassen.',
                'required' => false,
                'attr' => ['placeholder' => 'optional']
                ])
            ->add('accept', SubmitType::class, [
                'label' => 'Diesen Spieltermin verbindlich übernehmen',
                'attr' => [
                    'class' => 'btn-success',
                    'title' => 'Das wird schön!',
                ],
            ])
        ;
    }
}
