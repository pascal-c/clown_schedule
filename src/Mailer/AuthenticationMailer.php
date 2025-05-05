<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Service\AuthService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class AuthenticationMailer
{
    public function __construct(private MailerInterface $mailer, private AuthService $authService)
    {
    }

    public function sendInvitationMail(Clown $recipient, Clown $inviter): void
    {
        $loginToken = $this->authService->getLoginToken($recipient, '+1 week');
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($recipient->getEmail(), $recipient->getName()))
            ->subject('Einladung zur Spielplan App')
            ->htmlTemplate('emails/authentification/login_invitation.html.twig')
            ->context([
                'recipient' => $recipient,
                'inviter' => $inviter,
                'login_token' => $loginToken,
            ]);

        $this->mailer->send($email);
    }

    public function sendLoginByTokenMail(Clown $clown): void
    {
        $loginToken = $this->authService->getLoginToken($clown);
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Dein Anmeldelink für den Spielplan')
            ->htmlTemplate('emails/authentification/login_token.html.twig')
            ->context([
                'clown' => $clown,
                'login_token' => $loginToken,
            ]);

        $this->mailer->send($email);
    }

    public function sendChangePasswordByTokenMail(Clown $clown): void
    {
        $loginToken = $this->authService->getLoginToken($clown);
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Dein Link zum Ändern Deines Passwortes für den Clown Spielplan')
            ->htmlTemplate('emails/authentification/change_password_link.html.twig')
            ->context([
                'clown' => $clown,
                'login_token' => $loginToken,
            ]);

        $this->mailer->send($email);
    }
}
