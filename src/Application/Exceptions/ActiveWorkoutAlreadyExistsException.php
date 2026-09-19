<?php

declare(strict_types=1);

namespace GymLog\Application\Exceptions;

/**
 * У пользователя уже есть незавершённая тренировка (спека, раздел 3.2).
 * Примари-адаптер переведёт это в 409.
 */
final class ActiveWorkoutAlreadyExistsException extends \RuntimeException
{
    public static function forUser(string $userId): self
    {
        return new self("У пользователя {$userId} уже есть активная тренировка.");
    }
}
