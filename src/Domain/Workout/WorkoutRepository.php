<?php

declare(strict_types=1);

namespace GymLog\Domain\Workout;

/**
 * Порт хранилища агрегата «тренировка».
 *
 * Репозитория у WorkoutExercise и WorkoutSet нет намеренно: они внутри агрегата,
 * и добираться до них можно только через Workout. Иначе правило «редактировать
 * можно только активную тренировку» (Workout::assertEditable) станет обходимым.
 */
interface WorkoutRepository
{
    public function save(Workout $workout): void;

    /**
     * Жёсткое удаление вместе с упражнениями и подходами (спека, раздел 6):
     * orphanRemoval в маппинге плюс ON DELETE CASCADE в БД.
     */
    public function remove(Workout $workout): void;

    public function find(string $id): ?Workout;

    /**
     * Активная тренировка пользователя, если есть. Активная — это finishedAt IS NULL,
     * отдельного флага нет. Больше одной вернуться не может: держит частичный
     * уникальный индекс workout_active_per_user_uniq_index.
     */
    public function findActiveByUser(string $userId): ?Workout;

    /**
     * Последняя завершённая тренировка — источник для «повторить тренировку» (раздел 3.7).
     * Здесь нужен именно агрегат: из него копируются упражнения и подходы.
     */
    public function findLastFinishedByUser(string $userId): ?Workout;
}
