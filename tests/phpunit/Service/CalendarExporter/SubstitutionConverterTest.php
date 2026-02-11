<?php

declare(strict_types=1);

namespace App\Tests\Service\CalendarExporter;

use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Entity\Substitution;
use App\Repository\ConfigRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Service\CalendarExporter\SubstitutionConverter;
use App\Value\ScheduleStatus;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class SubstitutionConverterTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private PlayDateRepository&MockObject $playDateRepository;
    private ScheduleRepository&MockObject $scheduleRepository;
    private ConfigRepository&MockObject $configRepository;
    private SubstitutionConverter $converter;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);

        $this->converter = new SubstitutionConverter(
            $this->translator,
            $this->playDateRepository,
            $this->scheduleRepository,
            $this->configRepository,
        );
    }

    public function testGetSummary(): void
    {
        $substitution = (new Substitution())->setDaytime(TimeSlotPeriodInterface::AM);
        $this->translator->method('trans')->with(TimeSlotPeriodInterface::AM)->willReturn('matin');

        $summary = $this->converter->getSummary($substitution);

        $this->assertSame('Springer matin', $summary);
    }

    public function testGetDescription(): void
    {
        $playDate1 = (new PlayDate())->setTitle('Spiel 1')->setDaytime(TimeSlotPeriodInterface::PM);
        $playDate2 = (new PlayDate())->setTitle('Spiel 2')->setDaytime(TimeSlotPeriodInterface::ALL);
        $substitution = new Substitution();
        $this->playDateRepository->method('findConfirmedByTimeSlotPeriod')->willReturn([$playDate1, $playDate2]);
        $this->translator->method('trans')->willReturnMap([
            ['all', 'geht den ganzen Tag'],
            ['pm', 'nachmittags'],
        ]);
        $description = $this->converter->getDescription($substitution);

        $this->assertSame("Springer für:\nSpiel 1 - nachmittags\nSpiel 2 - geht den ganzen Tag", $description);
        // $this->assertStringContainsString("", $description)
    }

    public function testGetOccurence(): void
    {
        $substitution = (new Substitution())
            ->setDate(new DateTimeImmutable('2025-12-14'))
        ;

        $occurence = $this->converter->getOccurence($substitution);

        $this->assertInstanceOf(SingleDay::class, $occurence);
        $this->assertEquals(new DateTimeImmutable('2025-12-14'), $occurence->getDate()->getDateTime());
    }

    #[DataProvider('statusDataProvider')]
    public function testGetStatus(?Schedule $schedule, bool $isFeatureCalculationActive, EventStatus $expectedStatus): void
    {
        $date = new DateTimeImmutable('2025-12-14');
        $substitution = (new Substitution())->setDate($date);
        $this->scheduleRepository->method('find')->with(new Month($date))->willReturn($schedule);
        $this->configRepository->method('isFeatureCalculationActive')->willReturn($isFeatureCalculationActive);

        $status = $this->converter->getStatus($substitution);

        $this->assertSame($expectedStatus, $status);
    }

    public static function statusDataProvider(): Generator
    {
        yield 'when calculation is active and schedule is completed' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::COMPLETED),
            'isFeatureCalculationActive' => true,
            'expectedStatus' => EventStatus::CONFIRMED(),
        ];

        yield 'when calculation is active and schedule is not completed' => [
            'schedule' => (new Schedule())->setStatus(ScheduleStatus::IN_PROGRESS),
            'isFeatureCalculationActive' => true,
            'expectedStatus' => EventStatus::TENTATIVE(),
        ];

        yield 'when calculation is active and there is no schedule' => [
            'schedule' => null,
            'isFeatureCalculationActive' => true,
            'expectedStatus' => EventStatus::TENTATIVE(),
        ];

        yield 'when calculation is not active' => [
            'schedule' => null,
            'isFeatureCalculationActive' => false,
            'expectedStatus' => EventStatus::CONFIRMED(),
        ];
    }
}
