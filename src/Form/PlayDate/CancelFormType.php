<?php

namespace App\Form\PlayDate;

use App\Entity\PlayDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CancelFormType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Kommentar',
                'required' => false,
                'attr' => ['placeholder' => $this->translator->trans('Optional: Grund für die Absage...')],
            ])
            ->add('save', SubmitType::class, ['label' => 'Termin jetzt absagen'])
            ->setMethod('PUT')
            ->setAction($this->urlGenerator->generate('play_date_cancel', ['id' => $options['data']->getId()]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayDate::class,
        ]);
    }
}
