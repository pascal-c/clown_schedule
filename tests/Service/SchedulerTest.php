<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\Month;
use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Repository\ClownAvailabilityRepository;
use App\Repository\PlayDateRepository;
use App\Service\Scheduler;
use App\Service\Scheduler\AvailabilityChecker;
use App\Service\Scheduler\ClownAssigner;
use PHPUnit\Framework\TestCase;

final class SchedulerTest extends TestCase
{
    public function testcalculate(): void
    {
        $playDates = $this->getPlayDates();
        list($playDate1, $playDate2, $playDate3) = $playDates;
        
        $playDateRepository = $this->createMock(PlayDateRepository::class);
        $playDateRepository->expects($this->once())
            ->method('byMonth')
            ->willReturn($playDates);
        $clownAvailabilities = $this->getClownAvailabilities();
        $clownAvailabilityRepository = $this->createMock(ClownAvailabilityRepository::class);
        $clownAvailabilityRepository->expects($this->once())
            ->method('byMonth')
            ->willReturn($clownAvailabilities);
        $clownAssigner = $this->createMock(ClownAssigner::class);
        $clownAssigner->expects($this->exactly(3))
            ->method('assignFirstClown');
        $clownAssigner->expects($this->exactly(3))
            ->method('assignSecondClown')
            ->withConsecutive(
                [$playDate2, $clownAvailabilities],
                [$playDate3, $clownAvailabilities],
                [$playDate1, $clownAvailabilities],
            );
        
        $availabilityChecker = $this->createMock(AvailabilityChecker::class);
        $availabilityChecker->expects($this->any())
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


        $scheduler = new Scheduler($playDateRepository, $clownAvailabilityRepository, $clownAssigner, $availabilityChecker);
        $month = new Month(new \DateTimeImmutable('1978-12'));
        $scheduler->calculate($month);

        # remove existing clown assignments
        foreach($playDates as $playDate) {
            $this->assertEmpty($playDate->getPlayingClowns());
        }

        # calculate entitled plays
        $this->assertEquals(2.0, $clownAvailabilities[0]->getEntitledPlaysMonth()); # 6 * 1.0 / 3
        $this->assertEquals(1.6, $clownAvailabilities[1]->getEntitledPlaysMonth()); # 6 * 0.8 / 3
        $this->assertEquals(1.2, $clownAvailabilities[2]->getEntitledPlaysMonth()); # 6 * 0.6 / 3
        $this->assertEquals(1.2, $clownAvailabilities[3]->getEntitledPlaysMonth()); # 6 * 0.6 / 3
    }

    private function getPlayDates(): array
    {
        return [$this->buildPlayDate(), $this->buildPlayDate(), $this->buildPlayDate()];
    }

    private function buildPlayDate(): PlayDate
    {
        static $counter = 0; 
        $counter ++;
        $playDate = new PlayDate;
        $playDate->addPlayingClown(new Clown);
        $playDate->addPlayingClown(new Clown);
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

    private function buildClownAvailability(array $timeSlots): ClownAvailability
    {
        $clownAvailability = new ClownAvailability;
        foreach ($timeSlots as $availability => $number) {
            for ($i=0; $i<$number; $i++) {
                $timeSlot = new ClownAvailabilityTime;
                $timeSlot->setAvailability($availability);
                $clownAvailability->addClownAvailabilityTime($timeSlot);
            }
        }

        return $clownAvailability;
    }
}
