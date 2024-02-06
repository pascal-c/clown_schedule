<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Email', 'autocomplete' => 'username'],
                ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Passwort', 'autocomplete' => 'current-password', 'class' => 'collapse show password-forgot'],
                ])
            ->add('login', SubmitType::class, [
                'label' => 'anmelden',
                'attr' => ['title' => 'Mit Email und Passwort anmelden', 'class' => 'btn-primary collapse show password-forgot'],
                ])
            ->add('login_by_email', SubmitType::class, [
                'label' => 'Per Email-Link anmelden',
                'attr' => ['title' => 'Du bekommst eine Email, über Du Dich ohne Passwort anmelden kannst', 'class' => 'btn-primary collapse password-forgot'],
                ])
            ->add('change_password', SubmitType::class, [
                'label' => 'Passwort zurücksetzen',
                'attr' => ['title' => 'Du bekommst eine Email zum Ändern Deines Passwortes', 'class' => 'btn-primary collapse password-forgot'],
                ])
        ;
    }
}
