<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Service\AuthService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class AuthenticationMailer
{
    public function __construct(private MailerInterface $mailer, private AuthService $authService) {}

    public function sendLoginByTokenMail(Clown $clown)
    {
        $loginToken = $this->authService->getLoginToken($clown);
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clowns-und-clowns.de', 'Clowns Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Dein Anmeldelink für den Spielplan')
            ->htmlTemplate('emails/login_token.html.twig')
            ->context([
                'clown' => $clown,
                'login_token' => $loginToken,
            ]);

        $this->mailer->send($email);
    }
}
