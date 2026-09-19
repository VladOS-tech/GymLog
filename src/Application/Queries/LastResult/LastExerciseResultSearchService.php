<?php

declare(strict_types=1);

namespace GymLog\Application\Queries\LastResult;

/**
 * Порт стороны чтения. Лежит в Application, а не в Domain: домену эта выборка не нужна,
 * она существует только ради экрана упражнения.
 */
interface LastExerciseResultSearchService
{
    /**
     * Последний результат пользователя по упражнению или null, если упражнение
     * делается впервые — тогда сводки просто нет (спека, раздел 3.6).
     */
    public function findLastResult(string $userId, string $exerciseId): ?LastExerciseResult;
}
