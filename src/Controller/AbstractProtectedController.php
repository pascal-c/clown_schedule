<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Clown;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractProtectedController extends AbstractController
{
    private TranslatorInterface $translator;

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function translateMessage(string $message, array $parameters = []): string
    {
        return $this->translator->trans($message, $parameters);
    }

    protected function getTranslationLocale(): string
    {
        return $this->translator->getLocale();
    }

    protected function checkAuthorization(bool $isAuthorized): void
    {
        if (!$isAuthorized) {
            throw $this->createAccessDeniedException($this->translateMessage('Das darfst Du nicht.'));
        }
    }

    protected function adminOnly(): void
    {
        if (!$this->getCurrentClown()->isAdmin()) {
            throw $this->createAccessDeniedException($this->translateMessage('Das darfst Du nicht.'));
        }
    }

    protected function adminOrCurrentClownOnly(Clown $clown): void
    {
        if ($this->getCurrentClown() !== $clown) {
            $this->adminOnly();
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
