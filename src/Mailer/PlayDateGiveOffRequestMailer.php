<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use App\Repository\ClownRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class PlayDateGiveOffRequestMailer
{
    public function __construct(private MailerInterface $mailer, private ClownRepository $clownRepository)
    {
    }

    public function sendGiveOffRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receivers = array_map(
            fn (Clown $clown): Address => new Address($clown->getEmail(), $clown->getName()),
            $this->clownRepository->allActive(),
        );
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clowns-und-clowns.de', 'Clowns Spielplan'))
            ->to(...$receivers)
            ->subject($playDateChangeRequest->getRequestedBy()->getName().' möchte ein Spiel abgeben')
            ->htmlTemplate('emails/play_date_change_request/give-off_request.html.twig')
            ->context([
                'changeRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendAcceptGiveOffRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedBy();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clowns-und-clowns.de', 'Clowns Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject($playDateChangeRequest->getRequestedTo()->getName().' übernimmt Dein Spiel')
            ->htmlTemplate('emails/play_date_change_request/give-off_request_accept.html.twig')
            ->context([
                'changeRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }
}
