<?php

namespace App\Service;

use App\Entity\Clown;
use App\Repository\ClownRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthService
{
    private ?Clown $currentClown = null;

    public function __construct(private ClownRepository $clownRepository, private RequestStack $requestStack) {}

    public function login($email, $password): bool
    {
        $clown = $this->clownRepository->findOneByEmail($email);
        if (is_null($clown)) {
            return false;
        }

        $ok = password_verify($password, $clown->getPassword());
        if ($ok) {
            $session = $this->requestStack->getSession();
            $session->set('isLoggedIn', true);
            $session->set('currentClownId', $clown->getId());
        }
        return $ok;
    }

    public function logout(): void
    {
        $session = $this->requestStack->getSession();
        $session->set('isLoggedIn', false);
        $session->set('currentClownId', null);
        $this->currentClown = null;
    }

    public function isLoggedIn(): bool
    {
        $session = $this->requestStack->getSession();
        return $session->get('isLoggedIn', false);
    }

    public function getCurrentClown(): ?Clown
    {
        if (is_null($this->currentClown)) {
            $session = $this->requestStack->getSession();
            $this->currentClown = $this->clownRepository->find($session->get('currentClownId'));
        }

        return $this->currentClown;
    }
}
