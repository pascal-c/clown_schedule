<?php

namespace App\Form;

use App\Entity\Schedule;
use App\Value\ScheduleStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ScheduleCalculateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Schedule $schedule */
        $schedule = $builder->getData();
        if (null === $schedule) {
            $builder
                ->add('calculate', SubmitType::class, [
                    'label' => 'Spielplan erstellen', 
                    'attr' => array('onclick' => 'return confirm("Achtung! Alle vorhandenen Zuordnungen werden entfernt!")'),
            ]);
        } elseif (ScheduleStatus::IN_PROGRESS === $schedule->getStatus()) {
            $builder
                ->add('calculate', SubmitType::class, [
                    'label' => 'Spielplan neu erstellen', 
                    'attr' => array('onclick' => 'return confirm("Achtung! Alle vorhandenen Zuordnungen werden wieder entfernt!")'),
                ])
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
