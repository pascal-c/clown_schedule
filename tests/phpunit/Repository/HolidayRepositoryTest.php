<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\ConfigRepository;
use App\Repository\HolidayRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HolidayRepositoryTest extends TestCase
{
    private HolidayRepository $repository;
    private ConfigRepository&MockObject $configRepository;

    public function setUp(): void
    {
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->repository = new HolidayRepository($this->configRepository);
    }

    #[DataProvider('HolidayProviderSN')]
    public function testOneByDateForSaxony(DateTimeImmutable $date, bool $isHoliday, ?string $holidayName): void
    {
        $this->configRepository
            ->expects($this->once())
            ->method('getFederalState')
            ->willReturn('SN');

        $holidayName = $this->repository->oneByDate($date);
        $this->assertSame($isHoliday, null !== $holidayName);

        // call againg to test that the cache works
        $holidayName = $this->repository->oneByDate($date);
        $this->assertSame($holidayName, $holidayName);
    }

    public static function holidayProviderSN(): array
    {
        return [
            [new DateTimeImmutable('2022-01-01'), true, 'Neujahr'], // new year
            [new DateTimeImmutable('2022-01-06'), false, null], // three kings - no holiday in SN
            [new DateTimeImmutable('1974-04-12'), true, 'Karfreitag'], // Easter Friday
            [new DateTimeImmutable('1974-04-13'), false, null], // no holiday - Easter Saturday
            [new DateTimeImmutable('1974-04-14'), true, 'Ostersonntag'], // Easter Sunday
            [new DateTimeImmutable('1974-04-15'), true, 'Ostermontag'], // Easter Monday
            [new DateTimeImmutable('2023-05-18'), true, 'Himmelfahrt'], // trip to heaven
            [new DateTimeImmutable('2023-05-29'), true, 'Pfingsten'], // Pentecost
            [new DateTimeImmutable('2023-06-08'), false, null], // Corpus Christi - no holiday in SN
            [new DateTimeImmutable('2019-05-01'), true, 'Tag der Arbeit'], // day of work!
            [new DateTimeImmutable('2020-10-03'), true, 'Tag der deutschen Einheit'], // reunion day
            [new DateTimeImmutable('2021-10-31'), true, 'Reformationstag'], // reformation day
            [new DateTimeImmutable('2022-11-16'), true, 'Buß- und Bettag'], // bed and bus day
            [new DateTimeImmutable('2023-12-25'), true, '1. Weihnachtsfeiertag'], // chrismas 1
            [new DateTimeImmutable('2024-12-26'), true, '2. Weihnachtsfeiertag'], // chrismas 2
            [new DateTimeImmutable('2024-12-31'), false, null], // sylvester - no holiday
        ];
    }

    #[DataProvider('HolidayProviderHE')]
    public function testOneByDateForHessen(DateTimeImmutable $date, bool $isHoliday, ?string $holidayName): void
    {
        $this->configRepository
            ->expects($this->once())
            ->method('getFederalState')
            ->willReturn('HE');

        $holidayName = $this->repository->oneByDate($date);
        $this->assertSame($isHoliday, null !== $holidayName);

        // call againg to test that the cache works
        $holidayName = $this->repository->oneByDate($date);
        $this->assertSame($holidayName, $holidayName);
    }

    public static function holidayProviderHE(): array
    {
        return [
            [new DateTimeImmutable('2022-01-01'), true, 'Neujahr'], // new year
            [new DateTimeImmutable('2022-01-06'), false, null], // three kings - no holiday in HE
            [new DateTimeImmutable('1974-04-12'), true, 'Karfreitag'], // Easter Friday
            [new DateTimeImmutable('1974-04-13'), false, null], // no holiday - Easter Saturday
            [new DateTimeImmutable('1974-04-14'), true, 'Ostersonntag'], // Easter Sunday
            [new DateTimeImmutable('1974-04-15'), true, 'Ostermontag'], // Easter Monday
            [new DateTimeImmutable('2023-05-18'), true, 'Himmelfahrt'], // trip to heaven
            [new DateTimeImmutable('2023-05-29'), true, 'Pfingsten'], // Pentecost
            [new DateTimeImmutable('2023-06-08'), true, 'Fronleichnam'], // Corpus Christi
            [new DateTimeImmutable('2019-05-01'), true, 'Tag der Arbeit'], // day of work!
            [new DateTimeImmutable('2020-10-03'), true, 'Tag der deutschen Einheit'], // reunion day
            [new DateTimeImmutable('2021-10-31'), false, null], // reformation day - no holiday in HE
            [new DateTimeImmutable('2022-11-16'), false, null], // bed and bus day - no holiday in HE
            [new DateTimeImmutable('2023-12-25'), true, '1. Weihnachtsfeiertag'], // chrismas 1
            [new DateTimeImmutable('2024-12-26'), true, '2. Weihnachtsfeiertag'], // chrismas 2
            [new DateTimeImmutable('2024-12-31'), false, null], // sylvester - no holiday
        ];
    }
}
