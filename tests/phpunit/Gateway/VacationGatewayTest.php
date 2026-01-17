<?php

declare(strict_types=1);

namespace App\Tests\Gateway;

use App\Gateway\VacationGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class VacationGatewayTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private CacheInterface&MockObject $vacationCache;
    private VacationGateway $vacationGateway;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->vacationCache = $this->createMock(CacheInterface::class);
        $this->vacationGateway = new VacationGateway($this->httpClient, $this->vacationCache);
    }

    public function testFindByYearWhenFederalStateIsNull(): void
    {
        $result = $this->vacationGateway->findByYear(null, '2023');
        $this->assertSame([], $result);
    }

    public function testFindByYearReturnsCachedData(): void
    {
        $cacheItem = $this->createMock(ItemInterface::class);
        $this->vacationCache->method('get')
            ->willReturnCallback(function ($key, $callback) use ($cacheItem) {
                $this->assertSame('vacations 2023BY', $key);

                return $callback($cacheItem);
            });

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['holiday1', 'holiday2']);

        $this->httpClient->method('request')->willReturn($response);

        $result = $this->vacationGateway->findByYear('BY', '2023');
        $this->assertSame(['holiday1', 'holiday2'], $result);
    }

    public function testFindByYearHandlesClientException(): void
    {
        $cacheItem = $this->createMock(ItemInterface::class);
        $this->vacationCache->method('get')
            ->willReturnCallback(function ($key, $callback) use ($cacheItem) {
                $this->assertSame('vacations 2023BY', $key);

                return $callback($cacheItem);
            });

        $this->httpClient->method('request')->willThrowException(new ClientException(new MockResponse('error')));

        $result = $this->vacationGateway->findByYear('BY', '2023');
        $this->assertSame([], $result);
    }
}
