<?php

declare(strict_types=1);

namespace App\Tests\Service\CalendarExporter;

use App\Entity\Clown;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Repository\ScheduleRepository;
use App\Service\CalendarExporter\PlayDateConverter;
use App\Repository\SubstitutionRepository;
use App\Value\PlayDateType;
use App\Value\ScheduleStatus;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class PlayDateConverterTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private SubstitutionRepository&MockObject $substitutionRepository;
    private ScheduleRepository&MockObject $scheduleRepository;
    private PlayDateConverter $converter;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);

        $this->converter = new PlayDateConverter(
            $this->translator,
            $this->substitutionRepository,
            $this->scheduleRepository,
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

    public function testGetDescriptionForPlayDate(): void
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
        $this->translator->expects($this->once())->method('trans')->with('all')->willReturn('geht den ganzen Tag');

        $description = $this->converter->getDescription($playDate);

        $this->assertStringContainsString('Es spielen: Antonia, Biff', $description);
        $this->assertStringContainsString('Springer: Emil, Erika', $description);
        $this->assertStringContainsString('Tageszeit: geht den ganzen Tag', $description);
        $this->assertStringContainsString('Treffen: 12:45', $description);
        $this->assertStringContainsString('Spielzeit: 13:15-15:15', $description);
        $this->assertStringContainsString('Kommentar: Das wird schön!', $description);
    }

    public function testGetDescriptionForTraining(): void
    {
        $playDate = (new PlayDate())
            ->setDaytime(TimeSlotPeriodInterface::ALL)
            ->setType(PlayDateType::TRAINING)
            ->setComment('Das wird schön!')
            ->addPlayingClown((new Clown())->setName('Antonia'))
            ->addPlayingClown((new Clown())->setName('Biff'))
            ->setMeetingTime(new DateTimeImmutable('12:45'))
            ->setPlayTimeFrom(new DateTimeImmutable('13:15'))
            ->setPlayTimeTo(new DateTimeImmutable('15:15'))
        ;
        $this->translator->expects($this->once())->method('trans')->with('all')->willReturn('geht den ganzen Tag');

        $description = $this->converter->getDescription($playDate);

        $this->assertStringContainsString('Teilnehmende: Antonia, Biff', $description);
        $this->assertStringContainsString('Tageszeit: geht den ganzen Tag', $description);
        $this->assertStringContainsString('Treffen: 12:45', $description);
        $this->assertStringContainsString('Zeitraum: 13:15-15:15', $description);
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

    #[DataProvider('statusDataProvider')]
    public function testGetStatus(?Schedule $schedule, string $playDateStatus, EventStatus $expectedStatus): void
    {
        $date = new DateTimeImmutable('2025-12-14');
        $playDate = (new PlayDate())->setDate($date)->setStatus($playDateStatus);
        $this->scheduleRepository
            ->expects($this->atMost(1))
            ->method('find')
            ->with(new Month($date))
            ->willReturn($schedule);

        $status = $this->converter->getStatus($playDate);

        $this->assertSame($expectedStatus, $status);
    }

    public static function statusDataProvider(): Generator
    {
        yield 'when play date status is confirmed and schedule is completed' => [
            'schedule' => new Schedule()->setStatus(ScheduleStatus::COMPLETED),
            'playDateStatus' => PlayDate::STATUS_CONFIRMED,
            'expectedStatus' => EventStatus::CONFIRMED(),
        ];

        yield 'when play date status is in progress and schedule is not completed' => [
            'schedule' => new Schedule()->setStatus(ScheduleStatus::IN_PROGRESS),
            'playDateStatus' => PlayDate::STATUS_CONFIRMED,
            'expectedStatus' => EventStatus::TENTATIVE(),
        ];

        yield 'when play date status is not started and schedule is not completed' => [
            'schedule' => new Schedule()->setStatus(ScheduleStatus::NOT_STARTED),
            'playDateStatus' => PlayDate::STATUS_CONFIRMED,
            'expectedStatus' => EventStatus::TENTATIVE(),
        ];

        yield 'when play date status is cancelled' => [
            'schedule' => new Schedule(),
            'playDateStatus' => PlayDate::STATUS_CANCELLED,
            'expectedStatus' => EventStatus::CANCELLED(),
        ];

        yield 'when play date status is moved' => [
            'schedule' => new Schedule(),
            'playDateStatus' => PlayDate::STATUS_MOVED,
            'expectedStatus' => EventStatus::CANCELLED(),
        ];
    }
}
