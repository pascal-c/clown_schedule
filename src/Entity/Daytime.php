<?php

namespace App\Entity;

use App\Lib\Collection;

class Daytime
{
    public const AM = 'am';
    public const PM = 'pm';

    public static function getDaytimeOptions(): Collection
    {
        return new Collection([self::AM, self::PM]);
    }
}
