<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;

abstract class AbstractProtectedController extends AbstractController
{
    protected function adminOnly(): void
    {
        if (!$this->getCurrentClown()->isAdmin()) {
            throw $this->createAccessDeniedException('Das darfst Du nicht.');
        }
    }

    protected function createDeleteForm(string $url = '', string $label = ''): FormInterface
    {
        return $this->createFormBuilder()
            ->add(
                'delete',
                SubmitType::class,
                ['label' => $label]
            )
            ->setMethod('DELETE')
            ->setAction($url)
            ->getForm();
    }
}
