<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use App\Repository\ClownRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateGiveOffRequestMailer
{
    public function __construct(private MailerInterface $mailer, private ClownRepository $clownRepository, private TranslatorInterface $translator)
    {
    }

    public function sendGiveOffRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receivers = array_map(
            fn (Clown $clown): Address => new Address($clown->getEmail(), $clown->getName()),
            is_null($playDateChangeRequest->getRequestedTo()) ? $this->clownRepository->allActive() : [$playDateChangeRequest->getRequestedTo()],
        );
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(...$receivers)
            ->subject($this->translator->trans('%name% möchte ein Spiel abgeben', ['%name%' => $playDateChangeRequest->getRequestedBy()->getName()]))
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
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject($this->translator->trans('%name% übernimmt Dein Spiel', ['%name%' => $playDateChangeRequest->getRequestedTo()->getName()]))
            ->htmlTemplate('emails/play_date_change_request/give-off_request_accept.html.twig')
            ->context([
                'changeRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendCancelGiveOffRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receiver = $playDateChangeRequest->getRequestedTo();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($receiver->getEmail(), $receiver->getName()))
            ->subject($this->translator->trans('%name% hat seine Anfrage zurückgenommen', ['%name%' => $playDateChangeRequest->getRequestedBy()->getName()]))
            ->htmlTemplate('emails/play_date_change_request/give-off_request_cancel.html.twig')
            ->context([
                'changeRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendInformPartnersAboutChangeMail(PlayDateChangeRequest $playDateChangeRequest): void
    {
        $playDate = $playDateChangeRequest->getPlayDateToGiveOff();
        $oldPartner = $playDateChangeRequest->getRequestedBy();
        $newPartner = $playDateChangeRequest->getRequestedTo();
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
