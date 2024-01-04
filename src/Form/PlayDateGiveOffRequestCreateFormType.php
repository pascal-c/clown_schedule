<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PlayDateGiveOffRequestCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Hier kannst Du eine Nachricht an die anderen Clownis hinterlassen.',
                'required' => false,
                'attr' => ['placeholder' => 'optional']
                ])
            ->add('accept', SubmitType::class, [
                'label' => 'Abgabe-Anfrage senden',
            ])
        ;
    }
}
