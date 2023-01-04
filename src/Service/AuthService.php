<?php

namespace App\Service;

use App\Entity\Clown;
use App\Repository\ClownRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AuthService
{
    private ?Clown $currentClown = null;

    public function __construct(private ClownRepository $clownRepository, private RequestStack $requestStack,
        private TokenGeneratorInterface $tokenGenerator, private EntityManagerInterface $entityManager) {}

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
            $this->currentClown = $clown;
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
            $this->currentClown = $this->clownRepository->find(intval($session->get('currentClownId')));
        }

        return $this->currentClown;
    }

    public function getLoginToken(Clown $clown): string
    {
        $session = $this->requestStack->getSession();
        $token = $this->tokenGenerator->generateToken();
        $session->set('loginToken', $token);
        $session->set('loginTokenClownId', $clown->getId());
        return $token;
    }

    public function loginByToken(string $token): bool
    {
        $session = $this->requestStack->getSession();
    
        if ($token === $session->get('loginToken')) {
            $session->set('isLoggedIn', true);
            $session->set('currentClownId', $session->remove('loginTokenClownId'));
            $this->currentClown = null;

            $session->remove('loginToken');
            return true;
        }

        return false;
    }

    public function changePassword(string $password): void
    {
        $clown = $this->getCurrentClown();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $clown->setPassword($passwordHash);
        $this->entityManager->flush();
    }
}
