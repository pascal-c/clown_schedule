<?php

namespace App\Service;

use App\Entity\Clown;
use App\Entity\Token;
use App\Repository\ClownRepository;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AuthService
{
    private ?Clown $currentClown = null;

    public function __construct(
        private ClownRepository $clownRepository,
        private RequestStack $requestStack,
        private TokenGeneratorInterface $tokenGenerator,
        private EntityManagerInterface $entityManager,
        private TokenRepository $tokenRepository,
        private TimeService $timeService,
        private TokenService $tokenService,
    ) {
    }

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
        $this->tokenService->deleteExpired();
        $token = $this->tokenGenerator->generateToken();
        $tokenEntity = (new Token())
            ->setToken($token)
            ->setClown($clown)
            ->setExpiresAt($this->timeService->now()->modify('+1 hour'));
        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $token;
    }

    public function loginByToken(string $token): bool
    {
        $session = $this->requestStack->getSession();
        $tokenEntity = $this->tokenRepository->find($token);

        if ($tokenEntity) {
            $session->set('isLoggedIn', true);
            $session->set('currentClownId', $tokenEntity->getClown()->getId());
            $this->currentClown = $tokenEntity->getClown();

            $this->entityManager->remove($tokenEntity);
            $this->entityManager->flush();

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

    public function setLastUri(Request $request): void
    {
        $session = $this->requestStack->getSession();
        $uri = $request->getRequestUri();
        if (!$request->isXmlHttpRequest() && 'GET' === $request->getMethod() && !in_array($uri, ['', '/', '/dashboard'])) {
            $session->set('lastUri', $uri);
        }
    }

    public function getLastUri(): ?string
    {
        $session = $this->requestStack->getSession();
        if ($session->has('lastUri')) {
            $uri = $session->get('lastUri');
            $session->remove('lastUri');
        } else {
            $uri = null;
        }

        return $uri;
    }
}
