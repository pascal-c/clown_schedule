<?php

declare(strict_types=1);

namespace App\Tests\Guard;

use App\Entity\Clown;
use App\Entity\Invoice;
use App\Entity\Month;
use App\Guard\InvoiceGuard;
use App\Service\AuthService;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InvoiceGuardTest extends TestCase
{
    private InvoiceGuard $invoiceGuard;
    private AuthService&MockObject $authService;

    public function setUp(): void
    {
        $this->authService = $this->createMock(AuthService::class);
        $this->invoiceGuard = new InvoiceGuard($this->authService);
        parent::setUp();
    }

    #[DataProvider('canUpdateProvider')]
    public function testCanUpdate(bool $isAdmin, bool $isOwner, bool $expected): void
    {
        $clown = new Clown();
        $invoice = new Invoice(Month::build('2024-01'), $clown);

        $this->authService->expects($this->once())->method('isAdmin')->willReturn($isAdmin);
        $this->authService->method('getCurrentClown')->willReturn($isOwner ? $clown : new Clown());
        $this->assertEquals($expected, $this->invoiceGuard->canUpdate($invoice));
    }

    public static function canUpdateProvider(): Generator
    {
        yield 'when is admin' => [
            'isAdmin' => true,
            'isOwner' => false,
            'expected' => true,
        ];
        yield 'when is not admin but is owner' => [
            'isAdmin' => false,
            'isOwner' => true,
            'expected' => true,
        ];
        yield 'when is not admin and not owner' => [
            'isAdmin' => false,
            'isOwner' => false,
            'expected' => false,
        ];
    }
}
