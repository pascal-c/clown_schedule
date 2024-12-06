<?php

namespace App\Form;

use App\Entity\Fee;
use App\Service\TimeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeeFormType extends AbstractType
{
    public function __construct(private TimeService $timeService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('feeByPublicTransport', MoneyType::class, ['label' => 'Honorar Öffis', 'required' => false])
            ->add('feeByCar', MoneyType::class, ['label' => 'Honorar PKW', 'required' => false])
            ->add('feePerKilometer', MoneyType::class, ['label' => 'Kilometerpauschale', 'required' => true])
            ->add('kilometers', NumberType::class, ['label' => 'Kilometer', 'html5' => true, 'required' => false])
            ->add('kilometersFeeForAllClowns', CheckboxType::class, ['label' => 'Kilometergeld für beide Clowns', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Honorar speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fee::class,
        ]);
    }
}
