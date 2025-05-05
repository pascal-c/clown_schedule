<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class PlayDateSwapRequestMailer
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendSwapRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedTo();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject('Tauschanfrage von '.$playDateChangeRequest->getRequestedBy()->getName().'')
            ->htmlTemplate('emails/play_date_change_request/swap_request.html.twig')
            ->context([
                'swapRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendAcceptSwapRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedBy();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject('Tauschanfrage von '.$playDateChangeRequest->getRequestedTo()->getName().' angenommen!')
            ->htmlTemplate('emails/play_date_change_request/swap_request_accept.html.twig')
            ->context([
                'swapRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendDeclineSwapRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedBy();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject('Tauschanfrage von '.$playDateChangeRequest->getRequestedTo()->getName().' leider abgelehnt')
            ->htmlTemplate('emails/play_date_change_request/swap_request_decline.html.twig')
            ->context([
                'swapRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendCancelSwapRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedTo();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject($playDateChangeRequest->getRequestedBy()->getName().' hat seine Tauschanfrage zurückgenommen')
            ->htmlTemplate('emails/play_date_change_request/swap_request_cancel.html.twig')
            ->context([
                'swapRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    /**
     * send email to requesting and requested clown to inform about a request that has been closed.
     */
    public function sendSwapRequestClosedMail(PlayDateChangeRequest $playDateChangeRequest): void
    {
        $receiver = $playDateChangeRequest->getRequestedBy();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject('Die Tauschanfrage von '.$playDateChangeRequest->getRequestedBy()->getName().' hat sich erledigt')
            ->htmlTemplate('emails/play_date_change_request/swap_request_closed.html.twig')
            ->context([
                'swapRequest' => $playDateChangeRequest,
            ]);

        $receiver2 = $playDateChangeRequest->getRequestedTo();
        if (!is_null($receiver2)) {
            $email->addTo(new Address($receiver2->getEmail(), $receiver2->getName()));
        }

        $this->mailer->send($email);
    }

    public function sendInformPartnersAboutChangeMail(PlayDateChangeRequest $playDateChangeRequest): void
    {
        $playDate = $playDateChangeRequest->getPlayDateWanted();
        $oldPartner = $playDateChangeRequest->getRequestedTo();
        $newPartner = $playDateChangeRequest->getRequestedBy();
        $receivers = $playDate->getPlayingClowns()->filter(fn (Clown $clown): bool => $clown !== $newPartner);
        foreach ($receivers as $receiver) {
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
                ->to(new Address($receiver->getEmail(), $receiver->getName()))
                ->subject('Endlich mal wieder ein Spiel mit '.$newPartner->getName().'!')
                ->htmlTemplate('emails/play_date_change_request/change_request_inform_partner.html.twig')
                ->context([
                    'playDate' => $playDate,
                    'oldPartner' => $oldPartner,
                    'newPartner' => $newPartner,
                    'clown' => $receiver,
                ]);

            $this->mailer->send($email);
        }
    }
}
