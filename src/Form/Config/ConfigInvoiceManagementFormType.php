<?php

declare(strict_types=1);

namespace App\Form\Config;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigInvoiceManagementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('featureInvoiceManagementClowns', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature "Rechnungsverwaltung für Clownshonorare"',
                'label_attr' => ['class' => 'checkbox-switch'],
                'help' => 'Mit der Rechnungsverwaltung können Clowns-Honorare erfasst und durch eine monatliche Rechnungsansicht werden die Clowns in ihrer Rechnungsstellung bzw. das Büro bei der Kontrolle dieser unterstützt.',
                'attr' => [
                    'data-action' => 'toggle-by-checkbox#toggleVisibility',
                    'data-target' => 'toggle-by-checkbox.input',
                ],
            ])
            ->add('featureInvoicingClowns', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature "Rechnungsstellung Clowns"',
                'label_attr' => ['class' => 'checkbox-switch'],
                'help' => 'Clowns (oder das Büro) können ihre Rechnung als “gestellt” markieren. Clowns können selber einstellen, ob sie im Dashboard und/oder per Email über zu stellende Rechnungen benachrichtigt werden. Rechnungen, die bis zum 1. des übernächsten Monats nicht als gestellt markiert wurden, erscheinen im Büro-Dashboard in einer Liste nicht gestellter Rechnungen.',
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('featureInvoicePaymentClowns', CheckboxType::class, [
                'required' => false,
                'label' => 'Feature "Rechnungsbezahlung Clowns"',
                'label_attr' => ['class' => 'checkbox-switch'],
                'help' => 'Das Büro kann gestellte Clowns-Rechnungen als "bezahlt" markieren. Im Büro-Dashboard wird eine Liste zu bezahlender Rechnungen angezeigt.',
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('feeLabel', TextType::class, [
                'required' => true,
                'label' => 'Bezeichnung für Standard-Honorar',
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('alternativeFeeLabel', TextType::class, [
                'required' => false,
                'label' => 'Bezeichnung für alternatives Honorar',
                'help' => 'Wenn angegeben, kann ein alternatives Honorar angegeben werden. Wenn leer gelassen, wird kein alternatives Honorar angezeigt.',
                'row_attr' => [
                    'data-target' => 'toggle-by-checkbox.hideme',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
