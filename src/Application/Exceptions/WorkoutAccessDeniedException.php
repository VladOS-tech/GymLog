<?php

declare(strict_types=1);

namespace GymLog\Application\Exceptions;

/**
 * Тренировка принадлежит другому пользователю. Примари-адаптер переведёт это в 403.
 *
 * Проверка обязательна в каждом сценарии: id тренировки приходит от клиента,
 * и без неё чужую тренировку можно было бы править, просто подставив её id.
 */
final class WorkoutAccessDeniedException extends \RuntimeException
{
    public static function forWorkout(string $workoutId): self
    {
        return new self("Тренировка {$workoutId} принадлежит другому пользователю.");
    }
}
