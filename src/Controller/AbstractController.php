<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Clown;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractController extends SymfonyAbstractController
{
    protected AuthService $authService;

    #[Required]
    public function setAuthService(AuthService $authService)
    {
        $this->authService = $authService;
    }

    protected function adminOnly(): void
    {
        if (!$this->getCurrentClown()->isAdmin()) {
            throw $this->createAccessDeniedException('Das darfst Du nicht.');
        }
    }

    protected function getCurrentClown(): ?Clown
    {
        return $this->authService->getCurrentClown();
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return parent::render($view, array_merge($parameters, ['currentClown' => $this->getCurrentClown()]), $response);
    }
}
