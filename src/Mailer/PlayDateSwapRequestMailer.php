<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateSwapRequestMailer
{
    public function __construct(private MailerInterface $mailer, private TranslatorInterface $translator)
    {
    }

    public function sendSwapRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedTo();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject($this->translator->trans('Tauschanfrage von %name%', ['%name%' => $playDateChangeRequest->getRequestedBy()->getName()]))
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
            ->subject($this->translator->trans('Tauschanfrage von %name% angenommen!', ['%name%' => $playDateChangeRequest->getRequestedTo()->getName()]))
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
            ->subject($this->translator->trans('Tauschanfrage von %name% leider abgelehnt', ['%name%' => $playDateChangeRequest->getRequestedTo()->getName()]))
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
            ->subject($this->translator->trans('%name% hat seine Tauschanfrage zurückgenommen', ['%name%' => $playDateChangeRequest->getRequestedBy()->getName()]))
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
            ->subject($this->translator->trans('Die Tauschanfrage von %name% hat sich erledigt', ['%name%' => $playDateChangeRequest->getRequestedBy()->getName()]))
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
                ->subject($this->translator->trans('Endlich mal wieder ein Spiel mit %name%!', ['%name%' => $newPartner->getName()]))
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
