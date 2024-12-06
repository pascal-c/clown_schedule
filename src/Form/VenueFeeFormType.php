<?php

namespace App\Form;

use App\Entity\Venue;
use App\Service\TimeService;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class VenueFeeFormType extends FeeFormType
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
        ;

        parent::buildForm($builder, $options);
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
