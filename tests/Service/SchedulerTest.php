<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Schedule;
use App\Entity\Substitution;
use App\Entity\Venue;
use App\Factory\ScheduleFactory;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\SubstitutionRepository;
use App\Service\Scheduler;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use App\Service\Scheduler\FairPlayCalculator;
use App\Value\ScheduleStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SchedulerTest extends TestCase
{
    private PlayDateRepository|MockObject $playDateRepository;
    private ClownAvailabilityRepository|MockObject $clownAvailabilityRepository;
    private ClownAssigner|MockObject $clownAssigner;
    private AvailabilityChecker|MockObject $availabilityChecker;
    private FairPlayCalculator|MockObject $fairPlayCalculator;
    private SubstitutionRepository|MockObject $substitutionRepository;
    private ScheduleRepository|MockObject $scheduleRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private Scheduler $scheduler;

    public function setUp(): void
    {
        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->clownAvailabilityRepository = $this->createMock(ClownAvailabilityRepository::class);
        $this->clownAssigner = $this->createMock(ClownAssigner::class);
        $this->availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $this->fairPlayCalculator = $this->createMock(FairPlayCalculator::class);   
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->scheduler = new Scheduler(
            $this->playDateRepository, 
            $this->clownAvailabilityRepository, 
            $this->clownAssigner, 
            $this->availabilityChecker, 
            $this->fairPlayCalculator, 
            $this->substitutionRepository,
            $this->scheduleRepository,
            $this->entityManager,
        );
    }

    public function testCalculate(): void
    {
        $playDates = $this->getPlayDates();
        list($playDate1, $playDate2, $playDate3) = $playDates;
        $substitution = (new Substitution)->setSubstitutionClown(new Clown);
        
        $this->playDateRepository->expects($this->once())
            ->method('regularByMonth')
            ->willReturn($playDates);
        $clownAvailabilities = $this->getClownAvailabilities();
        $this->clownAvailabilityRepository->expects($this->once())
            ->method('byMonth')
            ->willReturn($clownAvailabilities);
        $this->clownAssigner->expects($this->exactly(3))
            ->method('assignFirstClown');
        $this->clownAssigner->expects($this->exactly(3))
            ->method('assignSecondClown')
            ->withConsecutive(
                [$playDate2, $clownAvailabilities],
                [$playDate3, $clownAvailabilities],
                [$playDate1, $clownAvailabilities],
            );
        $this->clownAssigner->expects($this->once())
            ->method('assignSubstitutionClown');    
        
        $this->availabilityChecker->expects($this->any())
            ->method('isAvailableFor')
            ->will($this->returnCallback(
                function(PlayDate $playDate, ClownAvailability $availability) use ($playDate1, $playDate2, $clownAvailabilities)
                {
                    if ($playDate === $playDate1) {
                        return false;
                    } elseif ($playDate === $playDate2) {
                        return true;
                    } else {
                        return $availability === $clownAvailabilities[0];
                    }
                }
            ));
 
        $this->fairPlayCalculator->expects($this->once())
            ->method('calculateEntitledPlays')
            ->with($clownAvailabilities, 6);
        $this->fairPlayCalculator->expects($this->once())
            ->method('calculateTargetPlays')
            ->with($clownAvailabilities, 6);
        $this->substitutionRepository->expects($this->once())
            ->method('byMonth')
            ->willReturn([$substitution]);

        $month = Month::build('1978-12');
        $this->scheduler->calculate($month);

        # remove existing clown assignments
        foreach($playDates as $playDate) {
            $this->assertEmpty($playDate->getPlayingClowns());
        }
        foreach($clownAvailabilities as $availability) {
            $this->assertNull($availability->getCalculatedPlaysMonth());
            $this->assertNull($availability->getCalculatedSubstitutions());
        }
        $this->assertNull($substitution->getSubstitutionClown());
    }

    public function testComplete_withAlreadyCompleted(): void
    {
        $month = Month::build('1978-12');
        $schedule = (new Schedule)
            ->setMonth($month)
            ->setStatus(ScheduleStatus::COMPLETED);

        $this->playDateRepository->expects($this->never())->method($this->anything());
        $this->clownAvailabilityRepository->expects($this->never())->method($this->anything());
        $this->clownAssigner->expects($this->never())->method($this->anything());
        $this->availabilityChecker->expects($this->never())->method($this->anything());
        $this->fairPlayCalculator->expects($this->never())->method($this->anything());
        $this->substitutionRepository->expects($this->never())->method($this->anything());
        $this->scheduleRepository->expects($this->once())->method('find')->with($month)->willReturn($schedule);
        $this->entityManager->expects($this->never())->method($this->anything());

        $result = $this->scheduler->complete($month);
        $this->assertNull($result);
    }

    public function testComplete_withNotCompleted(): void
    {
        $clown1 = $this->buildClown();
        $clown2 = $this->buildClown();
        $clown3 = $this->buildClown();
        $clown4 = $this->buildClown();
        $month = Month::build('1978-12');
        $schedule = (new Schedule)
            ->setMonth($month)
            ->setStatus(ScheduleStatus::IN_PROGRESS);
        $playDates = [$this->buildPlayDate($clown1, $clown2), $this->buildPlayDate($clown2, $clown4)];
        $clownAvailabilities = [
            $clown1->getId() => $this->buildClownAvailability(clown: $clown1),
            $clown2->getId() => $this->buildClownAvailability(clown: $clown2),
            $clown3->getId() => $this->buildClownAvailability(clown: $clown3),
        ];
        $substitution = (new Substitution())->setSubstitutionClown($clown1);
        $substitution2 = (new Substitution())->setSubstitutionClown($clown4);

        $this->playDateRepository->expects($this->once())->method('regularByMonth')->willReturn($playDates);
        $this->clownAvailabilityRepository->expects($this->once())->method('byMonth')->willReturn($clownAvailabilities);
        $this->clownAssigner->expects($this->never())->method($this->anything());
        $this->availabilityChecker->expects($this->never())->method($this->anything());
        $this->fairPlayCalculator->expects($this->never())->method($this->anything());
        $this->substitutionRepository->expects($this->once())->method('byMonth')->willReturn([$substitution, $substitution2]);
        $this->scheduleRepository->expects($this->once())->method('find')->with($month)->willReturn($schedule);
        $this->entityManager->expects($this->never())->method($this->anything());

        $result = $this->scheduler->complete($month);
        $this->assertSame($schedule, $result);
        $this->assertSame(1, $clownAvailabilities[$clown1->getId()]->getScheduledPlaysMonth());
        $this->assertSame(2, $clownAvailabilities[$clown2->getId()]->getScheduledPlaysMonth());
        $this->assertSame(0, $clownAvailabilities[$clown3->getId()]->getScheduledPlaysMonth());
        $this->assertSame(1, $clownAvailabilities[$clown1->getId()]->getScheduledSubstitutions());
        $this->assertSame(0, $clownAvailabilities[$clown2->getId()]->getScheduledSubstitutions());
        $this->assertSame(0, $clownAvailabilities[$clown3->getId()]->getScheduledSubstitutions());
    }

    private function getPlayDates(): array
    {
        return [$this->buildPlayDate(), $this->buildPlayDate(), $this->buildPlayDate()];
    }

    private function buildPlayDate(Clown $playingClown1 = null, Clown $playingClown2 = null): PlayDate
    {
        static $counter = 0; 
        $counter ++;
        $playDate = new PlayDate;
        $playDate->setDate(new \DateTimeImmutable('2018-12'));
        $playDate->setDaytime('pm');
        $playDate->addPlayingClown($playingClown1 ?? new Clown);
        $playDate->addPlayingClown($playingClown2 ?? new Clown);
        $playDate->setVenue((new Venue)->setName("Ort $counter"));
        return $playDate;
    }

    private function getClownAvailabilities(): array
    {
        return [
            $this->buildClownAvailability(['yes' => 30]), # ratio 1
            $this->buildClownAvailability(['yes' => 24, 'no' => 6]), # ratio 0.8
            $this->buildClownAvailability(['yes' => 18, 'no' => 12]), # ratio 0.6
            $this->buildClownAvailability(['yes' => 9, 'maybe' => 9, 'no' => 12]) # ratio 0.6
        ];
    }

    private function buildClownAvailability(array $timeSlots = [], Clown $clown = null): ClownAvailability
    {
        $clownAvailability = new ClownAvailability;
        $clownAvailability->setCalculatedPlaysMonth(27);
        $clownAvailability->setCalculatedSubstitutions(28);
        $clownAvailability->setScheduledPlaysMonth(29);
        $clownAvailability->setClown($clown);
        foreach ($timeSlots as $availability => $number) {
            for ($i=0; $i<$number; $i++) {
                $timeSlot = new ClownAvailabilityTime;
                $timeSlot->setAvailability($availability);
                $clownAvailability->addClownAvailabilityTime($timeSlot);
            }
        }

        return $clownAvailability;
    }

    private function buildClown(): Clown
    {
        static $id = 1;
        $clown = new Clown();
        $clown->setId($id);
        $id++;
        return $clown;
    }
}
