<?php
namespace App\Controller;

use App\Mailer\AuthenticationMailer;
use App\Repository\ClownRepository;
use App\Service\AuthService;
use Container77D1gjj\getDoctrine_Orm_DefaultEntityManager_PropertyInfoExtractorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(
        private ClownRepository $clownRepository, 
        private AuthService $authService,
        private AuthenticationMailer $mailer
        ) {}

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response 
    {
        $loginForm = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => false, 
                'attr' => ['placeholder' => 'Email', 'autocomplete' => 'username'],
                ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Passwort', 'autocomplete' => 'current-password']
                ])
            ->add('login', SubmitType::class, [
                'label' => 'anmelden',
                'attr' => ['title' => 'Mit Email und Passwort anmelden'],
                ])
            ->add('login_by_email', SubmitType::class, [
                'label' => 'per Email-Link anmelden (ohne Passwort)', 
                'attr' => ['title' => 'Du bekommst eine Email mit einem Anmelde-Link'],
                ])
            ->getForm();

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
            } 
            elseif ($loginForm['login_by_email']->isClicked()) {
                $clown = $this->clownRepository->findOneByEmail($loginForm['email']->getData());
                if (!is_null($clown)) {
                    $this->mailer->sendLoginByTokenMail($clown);
                }
                $this->addFlash('success', 
                    sprintf('Falls die Adresse richtig ist, wird ein Email mit einem Anmelde-Link an "%s" gesendet. Schau mal in Dein Email-Postfach!', $loginData['email']));
            }
        }

        return $this->renderForm('login/login.html.twig', [
            'clown' => $this->clownRepository->all(),
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

    #[Route('/logout', name: 'logout', methods: ['GET', 'POST'])]
    public function logout(Request $request): Response 
    {
        if (!$this->isCsrfTokenValid('logout_token', $request->query->get('logout_token'))) {
            $this->addFlash('warning', 'Logout ist schiefgegangen!');
            return $this->redirectToRoute('clown_index');
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
        $this->addFlash('success',
            sprintf('Herzlich Willkommen, %s! Schön, dass Du da bist.', $this->authService->getCurrentClown()->getName()));
        return $this->redirectToRoute('clown_index');
    }
}
