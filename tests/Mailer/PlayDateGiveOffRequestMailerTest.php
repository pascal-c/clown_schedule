<?php declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\PlayDateChangeRequest;
use App\Entity\PlayDateHistory;
use App\Mailer\PlayDateGiveOffRequestMailer;
use App\Repository\ClownRepository;
use App\Service\PlayDateChangeService;
use App\Service\PlayDateHistoryService;
use App\Service\TimeService;
use App\Value\PlayDateChangeReason;
use App\Value\PlayDateChangeRequestStatus;
use App\Value\PlayDateChangeRequestType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class PlayDateGiveOffRequestMailerTest extends TestCase
{
    private PlayDateGiveOffRequestMailer $playDateGiveOffRequestMailer;
    private MailerInterface|MockObject $mailer;
    private ClownRepository|MockObject $clownRepository;

    public function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->clownRepository = $this->createMock(ClownRepository::class);
        $this->playDateGiveOffRequestMailer = new PlayDateGiveOffRequestMailer(
            $this->mailer,
            $this->clownRepository,
        );
    }

    public function testSendGiveOffRequestMail(): void
    {
        $clown1 = (new Clown())->setEmail(('c1@clown.de'))->setName('Numero 1');
        $clown2 = (new Clown())->setEmail(('c2@clown.de'))->setName('Numero 2');
        $this->clownRepository->expects($this->once())->method('allActive')->willReturn([$clown1, $clown2]);

        $playDateChangeRequest = (new PlayDateChangeRequest())->setRequestedBy((new Clown)->setName('Emil'));

        $this->mailer->expects($this->once())->method('send')->with(
            self::callback(function(TemplatedEmail $email) use($playDateChangeRequest):bool {
                $receivers = $email->getTo();
                return 
                    count($receivers) === 2 &&
                    $receivers[0]->getName() === 'Numero 1' && $receivers[0]->getAddress() === 'c1@clown.de' &&
                    $receivers[1]->getName() === 'Numero 2' && $receivers[1]->getAddress() === 'c2@clown.de' &&
                    $email->getSubject() === 'Emil möchte ein Spiel abgeben' &&
                    $email->getContext() === [
                        'changeRequest' => $playDateChangeRequest,
                        'personalComment' => 'Hallöle',
                    ];
            }
        ));
        
        $this->playDateGiveOffRequestMailer->sendGiveOffRequestMail($playDateChangeRequest, 'Hallöle');
    }
}
