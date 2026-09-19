<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Workout\WorkoutException;

final readonly class AddSetCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * @return string id созданного подхода
     *
     * @throws EntityNotFoundException
     * @throws WorkoutException если тренировка уже завершена
     * @throws \InvalidArgumentException при недопустимых весе, повторах или отдыхе
     */
    public function handle(AddSetCommand $command): string
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $workoutExercise = $workout->findExercise($command->workoutExerciseId)
            ?? throw EntityNotFoundException::workoutExercise($command->workoutExerciseId);

        // id подхода генерит домен (UUIDv7), и он же задаёт порядок: колонки position нет.
        $set = $workoutExercise->addSet(
            $command->weightGrams,
            $command->reps,
            $command->type,
            $command->restAfterSeconds,
        );

        $this->domainSession->flush();

        return $set->getId();
    }
}
