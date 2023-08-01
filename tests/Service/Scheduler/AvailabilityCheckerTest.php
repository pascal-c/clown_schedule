<?php declare(strict_types=1);

namespace App\Tests\Service\Scheduler;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler\AvailabilityChecker;
use PHPUnit\Framework\TestCase;

final class AvailabilityCheckerTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsAvailableFor(
        ClownAvailability $clownAvailability, 
        array $otherPlayDates,
        string $firstClownGender, 
        bool $expectedResult,
        ?Substitution $isSubstitutionClown = null
    ): void
    {
        $playDate = $this->buildPlayDate('am', (new Clown)->setGender($firstClownGender));
        $playDateRepository = $this->createMock(PlayDateRepository::class);
        $playDateRepository->expects($this->atLeastOnce())
            ->method('byMonth')
            ->with($this->equalTo(Month::build('2022-04')))
            ->willReturn($otherPlayDates);
        $substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $substitutionRepository->expects($this->atMost(1))
            ->method('find')
            ->willReturn($isSubstitutionClown);

        $availabilityChecker = new AvailabilityChecker($playDateRepository, $substitutionRepository);
        $result = $availabilityChecker->isAvailableFor($playDate, $clownAvailability);
        $this->assertSame($expectedResult, $result);
    }

    public function dataProvider(): array
    {
        $clownAvailability = $this->buildClownAvailability('yes');
        $clownAvailabilityWithMaxPlaysDay2 = $this->buildClownAvailability('yes');
        $clownAvailabilityWithMaxPlaysDay2->setClown($clownAvailability->getClown());
        $clownAvailabilityWithMaxPlaysDay2->setMaxPlaysDay(2);

        $playDateOnSameTimeSlot = $this->buildPlayDate('am', $clownAvailability->getClown());
        $playDateOnSameDay = $this->buildPlayDate('pm', $clownAvailability->getClown());

        $substitution = $this->buildSubstitution()->setSubstitutionClown($clownAvailability->getClown());

        return [
            [$this->buildClownAvailability('yes'), [], 'male', true], # clown is available
            [$this->buildClownAvailability('maybe'), [], 'male', true], # clown is available
            [$this->buildClownAvailability('no'), [], 'male', false], # clown is not available
            [$this->buildClownAvailability('yes', true), [], 'male', false], # maxPlays reached
            [$this->buildClownAvailability('yes'), [$this->buildPlayDate()], 'male', true], # other play on same timeslot, but not for this clown
            [$clownAvailability, [], 'male', false, $substitution], # clown is available, but is already substitution clown
            [$clownAvailability, [$playDateOnSameTimeSlot], 'male', false], # other play on same timeslot for this clown
            [$clownAvailability, [$playDateOnSameDay], 'male', false], # other play on same day for this clown
            [$clownAvailabilityWithMaxPlaysDay2, [$playDateOnSameDay], 'male', true], # other play on same day for this clown but higher max
            [$this->buildClownAvailability('yes', false, 'male'), [], 'diverse', true], # one male one not
            [$this->buildClownAvailability('yes', false, 'male'), [], 'male', false], # two males
        ];
    }

    private function buildPlayDate(string $daytime = 'am', ?Clown $clown = null): PlayDate
    {
        $playDate = new PlayDate;
        $playDate->setDate(new \DateTimeImmutable('2022-04-01'));
        $playDate->setDaytime($daytime);
        if (!is_null($clown)) {
            $playDate->addPlayingClown($clown);
        }
        return $playDate;
    }

    private function buildSubstitution(string $daytime = 'am'): Substitution
    {
        return (new Substitution)
            ->setDate(new \DateTimeImmutable('2022-04-01'))
            ->setDaytime($daytime);
    }

    private function buildClownAvailability(
        string $availability, 
        bool $maxPlaysReached = false,
        string $gender = 'diverse'
    ): ClownAvailability
    {
        $clownAvailability = new ClownAvailability;
        $clownAvailability->setClown((new Clown)->setGender($gender));
        $clownAvailability->setMonth(Month::build('2022-04'));
        $clownAvailability->setMaxPlaysMonth(2);
        $clownAvailability->setCalculatedPlaysMonth($maxPlaysReached ? 2 : 1);
        $date = new \DateTimeImmutable('2022-04-01');
        $clownAvailability->addClownAvailabilityTime($this->buildAvailabilityTimeSlot($availability, $date, 'am'));

        return $clownAvailability;
    }

    private function buildAvailabilityTimeSlot(string $availability, \DateTimeInterface $date, string $daytime): ClownAvailabilityTime
    {
        $timeSlot = new ClownAvailabilityTime;
        $timeSlot->setAvailability($availability);
        $timeSlot->setDate($date);
        $timeSlot->setDaytime($daytime);
        return $timeSlot;
    }
}
