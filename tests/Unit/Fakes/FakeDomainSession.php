<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Fakes;

use GymLog\Application\Persistence\DomainSession;
use GymLog\Application\Persistence\UniqueConstraintViolationException;

/**
 * Фейковая единица работы. Считает вызовы flush и умеет один раз притвориться,
 * что база отвергла вставку по уникальному индексу — иначе гонку при старте
 * тренировки не проверить без настоящей БД.
 */
final class FakeDomainSession implements DomainSession
{
    public int $flushCount = 0;

    private bool $failNextFlush = false;

    public function failNextFlushWithUniqueViolation(): void
    {
        $this->failNextFlush = true;
    }

    public function flush(): void
    {
        ++$this->flushCount;

        if ($this->failNextFlush) {
            $this->failNextFlush = false;

            throw new UniqueConstraintViolationException('duplicate key value violates unique constraint');
        }
    }
}
