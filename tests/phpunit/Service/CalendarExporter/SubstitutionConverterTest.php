<?php

declare(strict_types=1);

namespace App\Tests\Service\CalendarExporter;

use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Repository\PlayDateRepository;
use App\Service\CalendarExporter\SubstitutionConverter;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SubstitutionConverterTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private PlayDateRepository&MockObject $playDateRepository;
    private SubstitutionConverter $converter;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);

        $this->converter = new SubstitutionConverter(
            $this->translator,
            $this->playDateRepository,
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
}
