<?php

declare(strict_types=1);

namespace GymLog\Application\Queries\LastResult;

use GymLog\Domain\Workout\SetType;

/**
 * Один подход в справке «прошлый раз». Не сущность и не часть агрегата:
 * это строка ответа, отсюда readonly и никаких методов изменения.
 */
final readonly class LastExerciseResultSet
{
    public function __construct(
        public int $weightGrams,
        public int $reps,
        public SetType $type,
    ) {
    }
}
