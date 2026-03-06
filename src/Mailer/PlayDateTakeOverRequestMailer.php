<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDateChangeRequest;
use App\Repository\ClownRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class PlayDateTakeOverRequestMailer
{
    public function __construct(private MailerInterface $mailer, private ClownRepository $clownRepository)
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
            ->subject('Möchtest Du ein Spiel übernehmen?')
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
                ->subject('Endlich mal wieder ein Spiel mit '.$newPartner->getName().'!')
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
