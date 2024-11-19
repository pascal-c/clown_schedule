<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Factory\ClownFactory;
use App\Factory\PlayDateChangeRequestFactory;
use App\Repository\PlayDateChangeRequestRepository;
use App\Value\PlayDateChangeRequestStatus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PlayDateChangeRequestRepositoryTest extends KernelTestCase
{
    private PlayDateChangeRequestRepository $repository;
    private ClownFactory $clownFactory;
    private PlayDateChangeRequestFactory $playDateChangeRequestFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->repository = $container->get(PlayDateChangeRequestRepository::class);

        $this->clownFactory = $container->get(ClownFactory::class);
        $this->playDateChangeRequestFactory = $container->get(PlayDateChangeRequestFactory::class);
    }

    public function testFindAllRequestsWaiting(): void
    {
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::CLOSED); // wrong status
        $one = $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING); // correct
        $two = $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING); // correct
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::ACCEPTED); // wrong status
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::DECLINED); // wrong status

        $result = $this->repository->findAllRequestsWaiting();
        $this->assertEqualsCanonicalizing([$one, $two], $result);
    }

    public function testFindSentRequestsWaiting(): void
    {
        $clown = $this->clownFactory->create();
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::CLOSED, requestedBy: $clown); // wrong status
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING); // wrong clown
        $findMe = $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING, requestedBy: $clown); // correct
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::ACCEPTED, requestedBy: $clown); // wrong status
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::DECLINED, requestedBy: $clown); // wrong status

        $result = $this->repository->findSentRequestsWaiting($clown);
        $this->assertEqualsCanonicalizing([$findMe], $result);
    }

    public function testFindReceivedRequestsWaiting(): void
    {
        $clown = $this->clownFactory->create();
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::CLOSED, requestedTo: $clown); // wrong status
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::ACCEPTED, requestedTo: $clown); // wrong status
        $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::DECLINED, requestedTo: $clown); // wrong status
        $one = $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING, requestedTo: $clown); // correct
        $two = $this->playDateChangeRequestFactory->create(status: PlayDateChangeRequestStatus::WAITING, requestedTo: null); // correct
        $this->playDateChangeRequestFactory->create(
            status: PlayDateChangeRequestStatus::WAITING,
            requestedTo: null,
            requestedBy: $clown
        ); // this is not a received request because it is requested by the clown herself

        $result = $this->repository->findReceivedRequestsWaiting($clown);
        $this->assertEqualsCanonicalizing([$one, $two], $result);
    }
}
