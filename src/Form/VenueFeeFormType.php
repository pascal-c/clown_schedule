<?php

namespace App\Form;

use App\Entity\Venue;
use App\Entity\VenueFee;
use App\Service\TimeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueFeeFormType extends AbstractType
{
    public function __construct(private TimeService $timeService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fee = $builder->getData();
        $builder
            ->add('validFrom', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Gültig ab',
                'input' => 'datetime_immutable',
                'disabled' => !is_null($fee->getId()),
                'required' => true,
                'constraints' => $this->validFromConstraints($fee->getVenue()),
            ])
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
            'data_class' => VenueFee::class,
        ]);
    }

    private function validFromConstraints(Venue $venue): array
    {
        $validFromConstraints = [
            new NotBlank(),
            new GreaterThanOrEqual($this->timeService->firstOfMonth()),
        ];

        $lastFee = $venue->getFees()->first();
        if ($lastFee && $lastFee->getValidFrom()) {
            $validFromConstraints[] = new GreaterThan($lastFee->getValidFrom());
        }

        return $validFromConstraints;
    }
}
