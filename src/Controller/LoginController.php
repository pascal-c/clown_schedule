<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginFormType;
use App\Mailer\AuthenticationMailer;
use App\Repository\ClownRepository;
use App\Service\AuthService;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

class LoginController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository,
        protected AuthService $authService,
        private AuthenticationMailer $mailer
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
        $passwordForm = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
                'options' => ['label' => false, 'constraints' => [new Length(['min' => 8])]],
                'required' => true,
                'first_options' => ['attr' => ['placeholder' => 'Neues Passwort', 'autocomplete' => 'new-password']],
                'second_options' => ['attr' => ['placeholder' => 'Neues Passwort Wiederholung', 'autocomplete' => 'new-password']],
            ])
            ->add('change_password', SubmitType::class, [
                'label' => 'Passwort ändern',
            ])
            ->setMethod('POST')
            ->getForm();
        $passwordForm->handleRequest($request);

        if (!$passwordForm->isSubmitted()) {
            $this->authService->loginByToken($token);
        }
        if (!$this->authService->isLoggedIn()) {
            $this->addFlash('warning', 'Das hat leider nicht geklappt. Der Link war scheinbar nicht mehr gültig. Bitte fordere eine neue Email an.');

            return $this->redirectToRoute('login');
        }
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $this->authService->changePassword($passwordForm['password']->getData());
            $this->addFlash(
                'success',
                sprintf('Super, Dein Passwort wurde geändert, %s!', $this->getCurrentClown()->getName())
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('login/change_password.html.twig', [
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
