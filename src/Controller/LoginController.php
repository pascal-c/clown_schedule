<?php
namespace App\Controller;

use App\Repository\ClownRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public function __construct(private ClownRepository $clownRepository, private AuthService $authService) {}

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response 
    {
        $loginForm = $this->createFormBuilder()
            ->add('email', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Email']])
            ->add('password', PasswordType::class, ['label' => false, 'attr' => ['placeholder' => 'Passwort']])
            ->add('login', SubmitType::class, ['label' => 'anmelden'])
            ->getForm();

        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() ) {
            $loginData = $loginForm->getData();
            $email = $loginData['email'];
            $password = $loginData['password'];

            if ($this->authService->login($email, $password)) {
                $this->addFlash('success', 
                    sprintf('Herzlich Willkommen, %s! Schön, dass Du da bist.', $this->authService->getCurrentClown()->getName()));
                return $this->redirectToRoute('clown_index');
            }
            
            $this->addFlash('warning', $this->getFailureMessage());
        }

        return $this->renderForm('login/login.html.twig', [
            'clown' => $this->clownRepository->all(),
            'active' => 'none',
            'form' => $loginForm,
        ]);
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
}
