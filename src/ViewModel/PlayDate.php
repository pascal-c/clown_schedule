<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\PlayDate as PlayDateEntity;
use BadMethodCallException;

class PlayDate
{
    public function __construct(
        public readonly PlayDateEntity $playDate,
        public readonly array $substitutionClowns,
        public readonly string $specialPlayDateUrl,
        public readonly bool $showChangeRequestLink,
        public readonly bool $showRegisterForTrainingLink,
    ) {
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->playDate, $name)) {
            return $this->playDate->$name(...$arguments);
        }
        if (method_exists($this->playDate, $getName = 'get'.ucfirst($name))) {
            return $this->playDate->$getName(...$arguments);
        }
        if (method_exists($this->playDate, $isName = 'is'.ucfirst($name))) {
            return $this->playDate->$isName(...$arguments);
        }
        if (method_exists($this->playDate, $hasName = 'has'.ucfirst($name))) {
            return $this->playDate->$hasName(...$arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist in PlayDate ViewModel', $name));
    }
}
