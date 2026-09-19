<?php

declare(strict_types=1);

namespace GymLog\Application\Queries\LastResult;

/**
 * Read-модель «что было в прошлый раз с этим упражнением» (спека, раздел 3.6).
 *
 * Спека сознательно отказалась от автоподстановки значений в поля ввода, поэтому
 * эта модель — справочная: её показывают рядом, а поля нового подхода остаются пустыми.
 */
final readonly class LastExerciseResult
{
    /**
     * @param LastExerciseResultSet[] $sets подходы в порядке выполнения
     */
    public function __construct(
        public string $workoutId,
        public \DateTimeImmutable $finishedAt,
        public array $sets,
    ) {
    }
}
