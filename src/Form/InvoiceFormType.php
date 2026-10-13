<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Service\AuthService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceFormType extends AbstractType
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Invoice $invoice */
        $invoice = $builder->getData();
        $isAdmin = $this->authService->isAdmin();
        $buttonClass = 'btn btn-sm btn-outline-primary';

        switch ($invoice?->getStatus()) {
            case Invoice::STATUS_OPEN:
                $builder->add('mark_issued', SubmitType::class, [
                    'label' => '> Rechnung als gestellt markieren',
                    'attr' => [
                        'class' => $buttonClass,
                        'title' => 'Die Rechnung wurde gestellt und verschickt.',
                    ],
                ]);
                break;
            case Invoice::STATUS_ISSUED:
                $builder->add('mark_open', SubmitType::class, [
                    'label' => '< Rechnung als offen markieren',
                    'attr' => [
                        'class' => $buttonClass,
                        'title' => 'Ich habe mich vertan, die Rechnung ist doch noch nicht gestellt.',
                    ],
                ]);
                if ($isAdmin) {
                    $builder->add('mark_paid', SubmitType::class, [
                        'label' => '> Rechnung als bezahlt markieren',
                        'attr' => [
                            'class' => $buttonClass,
                            'title' => 'Ich habe die Rechnung erhalten und bezahlt.',
                        ],
                    ]);
                }
                break;
            case Invoice::STATUS_PAID:
                if ($isAdmin) {
                    $builder->add('mark_issued', SubmitType::class, [
                        'label' => '< Rechnung als doch nicht bezahlt markieren',
                        'attr' => [
                            'class' => $buttonClass,
                            'title' => 'Ich habe mich vertan, die Rechnung ist doch noch nicht bezahlt.',
                        ],
                    ]);
                }
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
