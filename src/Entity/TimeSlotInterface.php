<?php

declare(strict_types=1);

namespace App\Entity;

interface TimeSlotInterface
{
    public function getDate(): ?\DateTimeImmutable;

    public function getDaytime(): ?string;
}
