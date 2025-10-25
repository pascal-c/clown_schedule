<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getClownConstraintsNavigationKey(): string
    {
        $session = $this->requestStack->getSession();

        return $session->get('clown_constraints_navigation_active', 'wishes');
    }

    public function setClownConstraintsNavigationKey($key): void
    {
        $session = $this->requestStack->getSession();
        $session->set('clown_constraints_navigation_active', $key);
    }
}
