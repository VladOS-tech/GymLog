<?php

declare(strict_types=1);

namespace GymLog\Application\Exceptions;

/**
 * Запрошенного объекта нет. Примари-адаптер переведёт это в 404.
 */
final class EntityNotFoundException extends \RuntimeException
{
    public static function workout(string $id): self
    {
        return new self("Тренировка {$id} не найдена.");
    }

    public static function workoutExercise(string $id): self
    {
        return new self("Упражнение тренировки {$id} не найдено.");
    }

    public static function set(string $id): self
    {
        return new self("Подход {$id} не найден.");
    }

    public static function exercise(string $id): self
    {
        return new self("Упражнение {$id} не найдено.");
    }

    public static function user(string $id): self
    {
        return new self("Пользователь {$id} не найден.");
    }
}
