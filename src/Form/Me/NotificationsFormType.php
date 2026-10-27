<?php

declare(strict_types=1);

namespace App\Form\Me;

use App\Entity\UserSettings;
use App\Service\ConfigService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationsFormType extends AbstractType
{
    public function __construct(private ConfigService $configService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduleCompletedEmail', CheckboxType::class, [
                'required' => false,
                'label' => 'Email bei Fertigstellung des Spielplans',
                'label_attr' => ['class' => 'checkbox-switch'],
                'help' => 'Du bekommst eine Email, wenn das Büro den Spielplan für den nächsten Monat fertig hat.',
            ]);

        if ($this->configService->hasFeatureInvoicingClowns()) {
            $builder
                ->add('openInvoiceOnDashboard', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Dashboard-Hinweis bei zu stellender Rechnung',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'help' => 'Du bekommst eine Benachrichtigung auf Deinem Dashboard, wenn Du eine neue Rechnung stellen kannst.',
                ])
                ->add('openInvoiceEmail', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Email bei zu stellender Rechnung',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'help' => 'Du bekommst eine Email, sobald Du eine neue Rechnung stellen kannst.',
                ])
                ->add('openInvoiceEmailReminder', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Email-Erinnerung bei zu stellender Rechnung',
                    'label_attr' => ['class' => 'checkbox-switch'],
                    'help' => 'Du bekommst nach 14 Tagen eine Email, wenn Du eine neue Rechnung noch nicht gestellt hast.',
                ]);
        }
        $builder
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettings::class,
        ]);
    }
}
