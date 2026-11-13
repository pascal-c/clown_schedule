<?php

declare(strict_types=1);

namespace App\Value;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum MeSubnavigationElement: string implements TranslatableInterface
{
    case PERSONAL_DATA = 'personal-data';
    case NOTIFICATIONS = 'notifications';
    case LANGUAGE = 'language';

    public function label(): string
    {
        return match($this) {
            static::PERSONAL_DATA => 'Persönliche Daten',
            static::NOTIFICATIONS => 'Benachrichtigungen',
            static::LANGUAGE => 'Sprache',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label, locale: $locale);
    }
}
