<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use App\Repository\ClownRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayDateTakeOverRequestMailer
{
    public function __construct(private MailerInterface $mailer, private ClownRepository $clownRepository, private TranslatorInterface $translator)
    {
    }

    public function sendTakeOverRequestMail(PlayDateChangeRequest $playDateChangeRequest, ?string $personalComment): void
    {
        $receivers = array_map(
            fn (Clown $clown): Address => new Address($clown->getEmail(), $clown->getName()),
            is_null($playDateChangeRequest->getRequestedTo()) ? $this->clownRepository->allActive() : [$playDateChangeRequest->getRequestedTo()],
        );
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(...$receivers)
            ->subject($this->translator->trans('Möchtest Du ein Spiel übernehmen?'))
            ->htmlTemplate('emails/play_date_change_request/take-over_request.html.twig')
            ->context([
                'changeRequest' => $playDateChangeRequest,
                'personalComment' => $personalComment,
            ]);

        $this->mailer->send($email);
    }

    public function sendInformPartnersAboutChangeMail(PlayDateChangeRequest $playDateChangeRequest): void
    {
        $playDate = $playDateChangeRequest->getPlayDateToGiveOff();
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
                    'oldPartner' => null,
                    'newPartner' => $newPartner,
                    'clown' => $receiver,
                ]);

            $this->mailer->send($email);
        }
    }
}
