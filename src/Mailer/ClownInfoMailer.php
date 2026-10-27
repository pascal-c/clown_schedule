<?php

namespace App\Mailer;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Schedule;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ClownInfoMailer
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendScheduleCompletedMail(Clown $clown, Schedule $schedule): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Spielplan für '.$schedule->getMonth()->getLabel().' ist fertig!')
            ->htmlTemplate('emails/clown_info/schedule_completed.html.twig')
            ->context([
                'clown' => $clown,
                'schedule' => $schedule,
            ]);

        $this->mailer->send($email);
    }

    public function sendInvoiceNotification(Invoice $invoice): void
    {
        $clown = $invoice->getClown();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Money! Money! Money!')
            ->htmlTemplate('emails/clown_info/invoice_notification.html.twig')
            ->context([
                'clown' => $clown,
                'month' => $invoice->getMonth(),
            ]);

        $this->mailer->send($email);
    }

    public function sendInvoiceNotificationReminder(Invoice $invoice): void
    {
        $clown = $invoice->getClown();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@clown-spielplan.de', 'Clown Spielplan'))
            ->to(new Address($clown->getEmail(), $clown->getName()))
            ->subject('Erinnerung: Money! Money! Money!')
            ->htmlTemplate('emails/clown_info/invoice_notification.html.twig')
            ->context([
                'clown' => $clown,
                'month' => $invoice->getMonth(),
            ]);

        $this->mailer->send($email);
    }
}
