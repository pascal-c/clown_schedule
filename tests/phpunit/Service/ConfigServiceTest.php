<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use App\Service\ConfigService;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfigServiceTest extends TestCase
{
    private ConfigService $configService;
    private ConfigRepository&MockObject $configRepository;

    public function setUp(): void
    {
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->configService = new ConfigService($this->configRepository);
    }

    #[DataProvider('featureInvoicingClownsProvider')]
    public function testFeatureInvoicingClowns(bool $featureInvoiceManagementClowns, bool $featureInvoicingClowns, bool $expected): void
    {
        $config = new Config();
        $config->featureInvoiceManagementClowns = $featureInvoiceManagementClowns;
        $config->featureInvoicingClowns = $featureInvoicingClowns;
        $this->configRepository->expects($this->atLeastOnce())->method('find')->willReturn($config);
        $this->assertSame($expected, $this->configService->hasFeatureInvoicingClowns());
    }

    public static function featureInvoicingClownsProvider(): Generator
    {
        yield 'when featureInvoiceManagementClowns ist true and featureInvoicingClowns is true' => [
            'featureInvoiceManagementClowns' => true,
            'featureInvoicingClowns' => true,
            'expected' => true,
        ];

        yield 'when featureInvoiceManagementClowns ist false and featureInvoicingClowns is true' => [
            'featureInvoiceManagementClowns' => false,
            'featureInvoicingClowns' => true,
            'expected' => false,
        ];
        yield 'when featureInvoiceManagementClowns ist true and featureInvoicingClowns is false' => [
            'featureInvoiceManagementClowns' => true,
            'featureInvoicingClowns' => false,
            'expected' => false,
        ];
        yield 'when featureInvoiceManagementClowns ist false and featureInvoicingClowns is false' => [
            'featureInvoiceManagementClowns' => false,
            'featureInvoicingClowns' => false,
            'expected' => false,
        ];
    }

    #[DataProvider('featureInvoicePaymentClownsProvider')]
    public function testFeatureInvoicePaymentClowns(bool $featureInvoiceManagementClowns, bool $featureInvoicePaymentClowns, bool $expected): void
    {
        $config = new Config();
        $config->featureInvoiceManagementClowns = $featureInvoiceManagementClowns;
        $config->featureInvoicePaymentClowns = $featureInvoicePaymentClowns;
        $this->configRepository->expects($this->atLeastOnce())->method('find')->willReturn($config);
        $this->assertSame($expected, $this->configService->hasFeatureInvoicePaymentClowns());
    }

    public static function featureInvoicePaymentClownsProvider(): Generator
    {
        yield 'when featureInvoiceManagementClowns ist true and featureInvoicePaymentClowns is true' => [
            'featureInvoiceManagementClowns' => true,
            'featureInvoicePaymentClowns' => true,
            'expected' => true,
        ];

        yield 'when featureInvoiceManagementClowns ist false and featureInvoicePaymentClowns is true' => [
            'featureInvoiceManagementClowns' => false,
            'featureInvoicePaymentClowns' => true,
            'expected' => false,
        ];
        yield 'when featureInvoiceManagementClowns ist true and featureInvoicePaymentClowns is false' => [
            'featureInvoiceManagementClowns' => true,
            'featureInvoicePaymentClowns' => false,
            'expected' => false,
        ];
        yield 'when featureInvoiceManagementClowns ist false and featureInvoicePaymentClowns is false' => [
            'featureInvoiceManagementClowns' => false,
            'featureInvoicePaymentClowns' => false,
            'expected' => false,
        ];
    }
}
