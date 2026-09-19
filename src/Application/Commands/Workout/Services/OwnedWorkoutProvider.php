<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout\Services;

use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Exceptions\WorkoutAccessDeniedException;
use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutRepository;

/**
 * Загрузка тренировки с проверкой владельца — повторяется в каждом сценарии,
 * поэтому вынесена в одно место.
 *
 * Почему это не метод репозитория: «принадлежит ли тренировка пользователю» —
 * правило доступа, а не способ достать данные. Репозиторий не должен знать,
 * от чьего имени его зовут.
 */
final readonly class OwnedWorkoutProvider
{
    public function __construct(
        private WorkoutRepository $workoutRepository,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     * @throws WorkoutAccessDeniedException
     */
    public function get(string $workoutId, string $userId): Workout
    {
        $workout = $this->workoutRepository->find($workoutId);

        if ($workout === null) {
            throw EntityNotFoundException::workout($workoutId);
        }

        if ($workout->getUser()->getId() !== $userId) {
            throw WorkoutAccessDeniedException::forWorkout($workoutId);
        }

        return $workout;
    }
}
