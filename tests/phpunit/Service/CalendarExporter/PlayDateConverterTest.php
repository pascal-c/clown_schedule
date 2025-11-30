<?php

declare(strict_types=1);

namespace App\Tests\Service\CalendarExporter;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Service\CalendarExporter\PlayDateConverter;
use App\Repository\SubstitutionRepository;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PlayDateConverterTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private SubstitutionRepository&MockObject $substitutionRepository;
    private PlayDateConverter $converter;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);

        $this->converter = new PlayDateConverter(
            $this->translator,
            $this->substitutionRepository,
        );
    }

    public function testGetName(): void
    {
        $playDate = new PlayDate();
        $playDate->setTitle('Auftritt');
        $playDate->setStatus(PlayDate::STATUS_CONFIRMED);

        $this->assertSame('Auftritt', $this->converter->getName($playDate));

        $playDate->setStatus(PlayDate::STATUS_CANCELLED);
        $this->assertSame('Auftritt ABGESAGT', $this->converter->getName($playDate));

        $playDate->setStatus(PlayDate::STATUS_MOVED);
        $this->assertSame('Auftritt VERSCHOBEN', $this->converter->getName($playDate));
    }

    public function testGetDescription(): void
    {
        $playDate = (new PlayDate())
            ->setDaytime(TimeSlotPeriodInterface::ALL)
            ->setComment('Das wird schön!')
            ->addPlayingClown((new Clown())->setName('Antonia'))
            ->addPlayingClown((new Clown())->setName('Biff'))
            ->setMeetingTime(new DateTimeImmutable('12:45'))
            ->setPlayTimeFrom(new DateTimeImmutable('13:15'))
            ->setPlayTimeTo(new DateTimeImmutable('15:15'))
        ;
        $substitution1 = (new Substitution())->setSubstitutionClown((new Clown())->setName('Emil'));
        $substitution2 = (new Substitution())->setSubstitutionClown((new Clown())->setName('Erika'));
        $this->substitutionRepository->method('findByTimeSlotPeriod')->willReturn([$substitution1, $substitution2]);
        $this->translator->method('trans')->with('all')->willReturn('geht den ganzen Tag');

        $description = $this->converter->getDescription($playDate);

        $this->assertStringContainsString('Es spielen: Antonia, Biff', $description);
        $this->assertStringContainsString('Springer: Emil, Erika', $description);
        $this->assertStringContainsString('Tageszeit: geht den ganzen Tag', $description);
        $this->assertStringContainsString('Treffen: 12:45', $description);
        $this->assertStringContainsString('Spielzeit: 13:15-15:15', $description);
        $this->assertStringContainsString('Kommentar: Das wird schön!', $description);
    }

    public function testGetLocation(): void
    {
        $venue = (new Venue())
            ->setName('Schauspielhaus');

        $playDate = (new PlayDate())->setVenue($venue);

        $location = $this->converter->getLocation($playDate);

        $this->assertSame('Schauspielhaus', strval($location));
    }

    public function testGetOccurenceWithTime(): void
    {
        $playDate = (new PlayDate())
            ->setDate(new DateTimeImmutable('2025-12-14'))
            ->setPlayTimeFrom(new DateTimeImmutable('13:15'))
            ->setPlayTimeTo(new DateTimeImmutable('15:15'))
        ;

        $occurence = $this->converter->getOccurence($playDate);

        $this->assertInstanceOf(TimeSpan::class, $occurence);
        $this->assertEquals(new DateTimeImmutable('2025-12-14 13:15'), $occurence->getBegin()->getDateTime());
        $this->assertEquals(new DateTimeImmutable('2025-12-14 15:15'), $occurence->getEnd()->getDateTime());
    }

    public function testGetOccurenceWithoutTime(): void
    {
        $playDate = (new PlayDate())
            ->setDate(new DateTimeImmutable('2025-12-14'))
        ;

        $occurence = $this->converter->getOccurence($playDate);

        $this->assertInstanceOf(SingleDay::class, $occurence);
        $this->assertEquals(new DateTimeImmutable('2025-12-14'), $occurence->getDate()->getDateTime());
    }
}
