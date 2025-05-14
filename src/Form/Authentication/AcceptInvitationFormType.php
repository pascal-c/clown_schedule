<?php

namespace App\Form\Authentication;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
            ->add('privacy_policy_accepted', CheckboxType::class, [
                'label' => sprintf('Ich akzeptiere diese fantastische <a href="%s" target="_blank">Datenschutzerklärung</a>.', $this->urlHelper->generate('privacy_policy')),
                'label_html' => true,
                'mapped' => false,
                'required' => true,
                'constraints' => new NotBlank(),
            ])
            ->add('accept_invitation', SubmitType::class, [
                'label' => 'Passwort setzen',
            ]);
    }
}
