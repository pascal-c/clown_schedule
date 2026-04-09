<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use App\Entity\PlayDateBundle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BundleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $bundle = $options['data'];

        $builder
            ->add('playDates', EntityType::class, [
                'class' => PlayDate::class,
                'choices' => $options['playDateChoices'],
                'choice_label' => fn (PlayDate $playDate) => $playDate->getDate()->format('d.m.Y').' '.$playDate->getDaytime().' '.$playDate->getName(),
                'required' => false,
                'label' => 'Spieltermine',
                'expanded' => true,
                'multiple' => true,
                'help' => 'Wähle bitte die Spieltermine aus, die Du bündeln möchtest.',
                'by_reference' => false,
            ])
            ->add(
                'cancel',
                SubmitType::class,
                [
                    'label' => 'abbrechen',
                    'attr' => ['class' => 'btn-link btn']]
            )
            ->add(
                'remove',
                SubmitType::class,
                [
                    'label' => 'Bündel auflösen',
                    'attr' => ['class' => 'btn-secondary btn', 'onclick' => 'return confirm("Möchtest du dieses Bündel wirklich auflösen?")']]
            )
            ->add('save', SubmitType::class, ['label' => 'speichern'])
            ->setMethod('PUT')
        ;

        if (null === $bundle->getId()) {
            $builder->remove('remove');
        } else {
            $builder->remove('cancel');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDateBundle::class,
            'playDateChoices' => [],
        ]);
    }
}
