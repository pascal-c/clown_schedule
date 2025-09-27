<?php

namespace App\Form;

use App\Entity\Schedule;
use App\Value\ScheduleStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleCompleteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Schedule $schedule */
        $schedule = $builder->getData();
        if (ScheduleStatus::COMPLETED !== $schedule?->getStatus()) {
            $builder
                ->add('complete', SubmitType::class, [
                    'label' => 'Spielplanerstellung jetzt abschließen',
                ])
                ->setMethod('PUT')
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
        ]);
    }
}
