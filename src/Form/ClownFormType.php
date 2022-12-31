<?php

namespace App\Form;

use App\Entity\Clown;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClownFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'weiblich' => 'female',
                    'divers' => 'diverse',
                    'männlich' => 'male',
                ],
                'label' => 'Gender',
                'expanded' => true,
                'multiple' => false,
                ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'aktiv?',
                'required' => false
                ])
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'Admin?',
                'required' => false
                ])
            ->add('save', SubmitType::class, ['label' => 'Clown speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Clown::class,
        ]);
    }
}
