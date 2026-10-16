<?php

namespace App\Form\Authentication;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordFormType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Die Passwörter stimmen nicht überein.',
            'options' => ['label' => false, 'constraints' => [new Length(min: 8)]],
            'required' => true,
            'first_options' => ['attr' => ['placeholder' => $this->translator->trans('Neues Passwort'), 'autocomplete' => 'new-password']],
            'second_options' => ['attr' => ['placeholder' => $this->translator->trans('Neues Passwort Wiederholung'), 'autocomplete' => 'new-password']],
        ]);
    }
}
