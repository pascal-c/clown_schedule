<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AcceptInvitationFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlHelper)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordFormType::class, [
                'label' => false,
            ])
            /*->add('accept_privacy_policy', CheckboxType::class, [
                'label' => sprintf('Ich akzeptiere diese fantastische <a href="%s">Datenschutzerklärung</a>.', $this->urlHelper->generate('privacy_policy')),
                'label_html' => true,
                'mapped' => false,
                'required' => true,
            ])*/
            ->add('accept_invitation', SubmitType::class, [
                'label' => 'Passwort setzen',
            ]);
    }
}
