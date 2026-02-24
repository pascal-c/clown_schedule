<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Clown;
use App\Entity\PlayDate;
use App\Entity\Substitution;
use App\Form\PlayDate\MoveFormType;
use App\Repository\PlayDateRepository;
use App\Repository\SubstitutionRepository;
use App\Service\ArrayCache;
use App\Service\AuthService;
use App\Service\PlayDateHistoryService;
use App\Service\PlayDateService;
use App\Value\PlayDateChangeReason;
use App\Value\TimeSlotPeriodInterface;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class PlayDateServiceTest extends KernelTestCase
{
    private PlayDateRepository&MockObject $playDateRepository;
    private SubstitutionRepository&MockObject $substitutionRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private PlayDateHistoryService&MockObject $playDateHistoryService;
    private AuthService&MockObject $authService;
    private ArrayCache&MockObject $cache;
    private PlayDateService $playDateService;

    private ContainerInterface $container;
    private Clown $currentClown;

    public function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();

        $this->playDateRepository = $this->createMock(PlayDateRepository::class);
        $this->substitutionRepository = $this->createMock(SubstitutionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->playDateHistoryService = $this->createMock(PlayDateHistoryService::class);
        $this->authService = $this->createMock(AuthService::class);
        $this->cache = $this->createMock(ArrayCache::class);
        $this->playDateService = new PlayDateService(
            $this->playDateRepository,
            $this->substitutionRepository,
            $this->entityManager,
            $this->playDateHistoryService,
            $this->authService,
            $this->cache,
        );

        $this->currentClown = new Clown();
        $this->authService->method('getCurrentClown')->willReturn($this->currentClown);
    }

    public function testCancelSuccessfully(): void
    {
        $playDate = new PlayDate();
        $substitution = (new Substitution())->setDate(new DateTimeImmutable('2024-12'));
        $this->playDateRepository->expects($this->once())->method('findConfirmedByTimeSlotPeriod')->willReturn([$playDate]);
        $this->substitutionRepository->expects($this->once())->method('findByTimeSlotPeriod')->willReturn([$substitution]);
        $this->substitutionRepository->expects($this->once())->method('byMonthCacheKey')->willReturn('key');
        $this->entityManager->expects($this->once())->method('remove')->with($substitution);
        $this->cache->expects($this->once())->method('remove')->with('key');
        $this->playDateHistoryService->expects($this->once())->method('add')->with(
            $playDate,
            $this->currentClown,
            PlayDateChangeReason::CANCEL,
        );

        $this->playDateService->cancel($playDate);
        $this->assertTrue($playDate->isCancelled());
    }

    public function testCancelSuccessfullyWhenOtherPlayDatesExist(): void
    {
        $playDate = new PlayDate();
        $substitution = (new Substitution())->setDate(new DateTimeImmutable('2024-12'));
        $this->playDateRepository->expects($this->once())->method('findConfirmedByTimeSlotPeriod')->willReturn([$playDate, new PlayDate()]);
        $this->substitutionRepository->expects($this->never())->method('findByTimeSlotPeriod');
        $this->entityManager->expects($this->never())->method('remove')->with($substitution);
        $this->playDateHistoryService->expects($this->once())->method('add')->with(
            $playDate,
            $this->currentClown,
            PlayDateChangeReason::CANCEL,
        );

        $this->playDateService->cancel($playDate);
        $this->assertTrue($playDate->isCancelled());
    }

    public function testMoveSuccessfully(): void
    {
        $playDate = (new PlayDate())->setId(317)->setTitle('Spieltermin');
        $moveForm = $this->container->get('form.factory')->create(MoveFormType::class, $playDate);
        $moveForm['date']->setData(new DateTimeImmutable('2028-12-20'));
        $moveForm['daytime']->setData(TimeSlotPeriodInterface::PM);
        $moveForm['meetingTime']->setData(new DateTimeImmutable('14:00'));
        $moveForm['playTimeFrom']->setData(new DateTimeImmutable('15:00'));
        $moveForm['playTimeTo']->setData(new DateTimeImmutable('17:00'));
        $substitution = (new Substitution())->setDate(new DateTimeImmutable('2024-12'));

        $this->playDateRepository->expects($this->once())->method('findConfirmedByTimeSlotPeriod')->willReturn([$playDate]);
        $this->substitutionRepository->expects($this->once())->method('findByTimeSlotPeriod')->willReturn([$substitution]);
        $this->substitutionRepository->expects($this->once())->method('byMonthCacheKey')->willReturn('key');
        $this->entityManager->expects($this->once())->method('remove')->with($substitution);
        $this->entityManager->expects($this->once())->method('persist');
        $this->cache->expects($this->once())->method('remove')->with('key');
        $this->playDateHistoryService
            ->expects($invocationRule = $this->exactly(2))
            ->method('add')
            ->willReturnCallback(
                function (PlayDate $givenPlayDate, Clown $clown, PlayDateChangeReason $playDateChangeReason) use ($invocationRule, $playDate) {
                    match ($invocationRule->numberOfInvocations()) {
                        1 =>  $this->assertSame([$playDate, $this->currentClown, PlayDateChangeReason::MOVE], [$givenPlayDate, $clown, $playDateChangeReason]),
                        2 =>  $this->assertSame([$playDate->getMovedTo(), $this->currentClown, PlayDateChangeReason::CREATE], [$givenPlayDate, $clown, $playDateChangeReason]),
                    };
                }
            );

        $this->playDateService->move($playDate, $moveForm);
        $this->assertTrue($playDate->isMoved());

        $this->assertSame('Spieltermin', $playDate->getMovedTo()->getTitle());
        $this->assertEquals(new DateTimeImmutable('2028-12-20'), $playDate->getMovedTo()->getDate());
        $this->assertSame(TimeSlotPeriodInterface::PM, $playDate->getMovedTo()->getDaytime());
        $this->assertSame('14:00', $playDate->getMovedTo()->getMeetingTime()->format('H:i'));
        $this->assertSame('15:00', $playDate->getMovedTo()->getPlayTimeFrom()->format('H:i'));
        $this->assertSame('17:00', $playDate->getMovedTo()->getPlayTimeTo()->format('H:i'));
    }
}
