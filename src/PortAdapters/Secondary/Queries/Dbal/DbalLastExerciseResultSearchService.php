<?php

declare(strict_types=1);

namespace GymLog\PortAdapters\Secondary\Queries\Dbal;

use Doctrine\DBAL\Connection;
use GymLog\Application\Queries\LastResult\LastExerciseResult;
use GymLog\Application\Queries\LastResult\LastExerciseResultSearchService;
use GymLog\Application\Queries\LastResult\LastExerciseResultSet;
use GymLog\Domain\Workout\SetType;

/**
 * Чтение мимо ORM, на сыром SQL через DBAL.
 *
 * Через репозиторий пришлось бы поднять весь агрегат Workout — все упражнения той
 * тренировки и все их подходы, — чтобы показать сводку по одному упражнению.
 * Здесь один запрос возвращает ровно нужные строки.
 */
final readonly class DbalLastExerciseResultSearchService implements LastExerciseResultSearchService
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findLastResult(string $userId, string $exerciseId): ?LastExerciseResult
    {
        // CTE находит не тренировку, а сразу нужную строку workout_exercise — тогда каждый
        // параметр используется ровно один раз, и не приходится повторять :exerciseId снаружи.
        //
        // Сортировка по position вторым ключом делает выбор однозначным в редком случае,
        // когда одно упражнение добавлено в тренировку дважды: берём первое по порядку.
        //
        // ORDER BY s.id в финальной выборке — это и есть порядок создания подходов:
        // id у нас монотонный UUIDv7, отдельной колонки position у подхода нет.
        $sql = <<<'SQL'
            WITH last_workout_exercise AS (
                SELECT we.id AS workout_exercise_id,
                       w.id  AS workout_id,
                       w.finished_at
                FROM workout_exercise we
                INNER JOIN workout w ON w.id = we.workout_id
                WHERE w.user_id = :userId
                  AND we.exercise_id = :exerciseId
                  AND w.finished_at IS NOT NULL
                ORDER BY w.finished_at DESC, we.position ASC
                LIMIT 1
            )
            SELECT lwe.workout_id,
                   lwe.finished_at,
                   s.weight_grams,
                   s.reps,
                   s.type
            FROM last_workout_exercise lwe
            INNER JOIN workout_set s ON s.workout_exercise_id = lwe.workout_exercise_id
            ORDER BY s.id
            SQL;

        $rows = $this->connection->fetchAllAssociative($sql, [
            'userId' => $userId,
            'exerciseId' => $exerciseId,
        ]);

        // Ноль строк — либо упражнение делается впервые, либо в прошлый раз его
        // добавили в тренировку, но не записали ни одного подхода. Показывать нечего в обоих случаях.
        if ($rows === []) {
            return null;
        }

        $sets = array_map(
            static fn (array $row): LastExerciseResultSet => new LastExerciseResultSet(
                // Приведение обязательно: pdo_pgsql отдаёт числовые колонки строками.
                (int) $row['weight_grams'],
                (int) $row['reps'],
                SetType::from((string) $row['type']),
            ),
            $rows,
        );

        return new LastExerciseResult(
            (string) $rows[0]['workout_id'],
            new \DateTimeImmutable((string) $rows[0]['finished_at']),
            $sets,
        );
    }
}
