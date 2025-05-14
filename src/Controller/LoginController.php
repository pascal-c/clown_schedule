<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Authentication\AcceptInvitationFormType;
use App\Form\Authentication\ChangePasswordFormType;
use App\Form\Authentication\LoginFormType;
use App\Mailer\AuthenticationMailer;
use App\Repository\ClownRepository;
use App\Service\AuthService;
use App\Service\TimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository,
        protected AuthService $authService,
        private AuthenticationMailer $mailer,
        private TimeService $timeService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $loginForm = $this->createForm(LoginFormType::class);

        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $loginData = $loginForm->getData();
            $email = $loginData['email'];
            $password = $loginData['password'];

            if ($loginForm['login']->isClicked()) {
                if ($this->authService->login($email, $password)) {
                    return $this->handleLoginSuccess();
                }

                $this->addFlash('warning', $this->getFailureMessage());
            } elseif ($loginForm['login_by_email']->isClicked()) {
                $clown = $this->clownRepository->findOneByEmail($loginForm['email']->getData());
                if (!is_null($clown)) {
                    $this->mailer->sendLoginByTokenMail($clown);
                }
                $this->addFlash(
                    'success',
                    sprintf('Falls die Adresse richtig ist, wird ein Email mit einem Anmelde-Link an "%s" gesendet. Schau mal in Dein Email-Postfach!', $loginData['email'])
                );
            } elseif ($loginForm['change_password']->isClicked()) {
                $clown = $this->clownRepository->findOneByEmail($loginForm['email']->getData());
                if (!is_null($clown)) {
                    $this->mailer->sendChangePasswordByTokenMail($clown);
                }
                $this->addFlash(
                    'success',
                    sprintf('Falls die Adresse richtig ist, wird ein Email mit einem Link zum Ändern Deines Passwortes an "%s" gesendet. Schau mal in Dein Email-Postfach!', $loginData['email'])
                );
            }
        }

        return $this->render('login/login.html.twig', [
            'active' => 'none',
            'form' => $loginForm,
        ]);
    }

    #[Route('/login/{token}', name: 'login_by_token', methods: ['GET'])]
    public function loginByToken(string $token): Response
    {
        if ($this->authService->loginByToken($token)) {
            return $this->handleLoginSuccess();
        }

        $this->addFlash('warning', 'Das hat leider nicht geklappt. Der Link war scheinbar nicht mehr gültig. Bitte fordere eine neue Email an.');

        return $this->redirectToRoute('login');
    }

    #[Route('/change_password/{token}', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, string $token): Response
    {
        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        $passwordForm->handleRequest($request);

        if (!$passwordForm->isSubmitted()) {
            $this->authService->loginByToken($token);
        }
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Der Link war scheinbar nicht mehr gültig. Bitte fordere eine neue Email an.');

            return $this->redirectToRoute('login');
        }
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $this->authService->changePassword($passwordForm['password']['password']->getData());
            $this->addFlash(
                'success',
                sprintf('Super, Dein Passwort wurde geändert, %s!', $this->getCurrentClown()->getName())
            );
            $this->authService->logout();

            return $this->redirectToRoute('login');
        }

        return $this->render('login/change_password.html.twig', [
            'active' => 'none',
            'form' => $passwordForm,
        ]);
    }

    #[Route('/accept-invitation/{token}', name: 'accept_invitation', methods: ['GET', 'POST'])]
    public function acceptInvitation(Request $request, string $token): Response
    {
        $passwordForm = $this->createForm(AcceptInvitationFormType::class);
        $passwordForm->handleRequest($request);

        if (!$passwordForm->isSubmitted()) {
            $this->authService->loginByToken($token);
        }
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Der Einladungslink war scheinbar nicht mehr gültig. Tut mir leid!');

            return $this->redirectToRoute('login');
        }
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $this->authService->changePassword($passwordForm['password']['password']->getData());
            $currentClown = $this->getCurrentClown();
            $currentClown->setPrivacyPolicyAccepted($passwordForm['privacy_policy_accepted']->getData());
            $currentClown->setPrivacyPolicyDateTime($this->timeService->now());
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                sprintf('Super, Dein Zugang wurde erstellt! Du kannst Dich jetzt anmelden, %s!', $currentClown->getName())
            );
            $this->authService->logout();

            return $this->redirectToRoute('login');
        }

        return $this->render('login/accept_invitation.html.twig', [
            'active' => 'none',
            'form' => $passwordForm,
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET', 'POST'])]
    public function logout(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('logout_token', $request->query->get('logout_token'))) {
            $this->addFlash('warning', 'Logout ist schiefgegangen!');

            return $this->redirectToRoute('root');
        }

        $this->authService->logout();
        $this->addFlash('success', 'Du wurdest mit großem Erfolg abgemeldet. Bis bald!');

        return $this->redirectToRoute('login');
    }

    private function getFailureMessage(): string
    {
        $messages = [
            'Das hat leider nicht geklappt!',
            'Email oder Passwort nicht korrekt!',
            'So nicht, Freundchen!',
            'Da war leider etwas falsch. Bitte nochmal probieren.',
            'Das war wohl nicht ganz richtig! Wer tippen kann, ist klar im Vorteil!',
        ];

        return $messages[array_rand($messages)];
    }

    private function handleLoginSuccess(): Response
    {
        $this->addFlash(
            'success',
            sprintf('Herzlich Willkommen, %s! Schön, dass Du da bist.', $this->getCurrentClown()->getName())
        );

        $uri = $this->authService->getLastUri();
        if (!is_null($uri)) {
            return $this->redirect($uri);
        }

        return $this->redirectToRoute('root');
    }
}
