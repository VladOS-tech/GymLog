<?php

declare(strict_types=1);

namespace GymLog\Application\Exceptions;

/**
 * Архивное упражнение нельзя добавить в тренировку: оно убрано из списка выбора
 * (спека, раздел 6). Старые тренировки с ним при этом продолжают существовать.
 */
final class ExerciseArchivedException extends \RuntimeException
{
    public static function forExercise(string $exerciseId): self
    {
        return new self("Упражнение {$exerciseId} архивировано и недоступно для выбора.");
    }
}
