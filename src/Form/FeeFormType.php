<?php

namespace App\Form;

use App\Entity\Fee;
use App\Repository\ConfigRepository;
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
    public function __construct(protected TimeService $timeService, protected ConfigRepository $configRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config = $this->configRepository->find();

        if ($config->isFeatureFeeActive()) {
            $builder->add('feeByPublicTransport', MoneyType::class, ['label' => $config->getFeeLabel(), 'required' => false]);
        }
        if ($config->isFeatureAlternativeFeeActive()) {
            $builder->add('feeByCar', MoneyType::class, ['label' => $config->getAlternativeFeeLabel(), 'required' => false]);
        }

        $builder
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
