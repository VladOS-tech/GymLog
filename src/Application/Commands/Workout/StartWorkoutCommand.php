<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

/**
 * Команда — это данные запроса и ничего больше: ни поведения, ни зависимостей.
 *
 * Свойства публичные и readonly, в отличие от образца с приватными полями и геттерами:
 * на PHP 8.4 геттеры к неизменяемому DTO не добавляют ничего, кроме строк.
 */
final readonly class StartWorkoutCommand
{
    public function __construct(
        public string $userId,
    ) {
    }
}
