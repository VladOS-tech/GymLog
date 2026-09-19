<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Workout\WorkoutException;

final readonly class UpdateSetCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * Удаления подхода в MVP нет: пока тренировка активна, подход редактируется,
     * после завершения — только чтение (спека, раздел 6). Проверку «активна» делает
     * сам WorkoutSet::assign, поэтому обойти её через этот сценарий нельзя.
     *
     * @throws EntityNotFoundException
     * @throws WorkoutException если тренировка уже завершена
     * @throws \InvalidArgumentException при недопустимых весе, повторах или отдыхе
     */
    public function handle(UpdateSetCommand $command): void
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $workoutExercise = $workout->findExercise($command->workoutExerciseId)
            ?? throw EntityNotFoundException::workoutExercise($command->workoutExerciseId);

        $set = $workoutExercise->findSet($command->setId)
            ?? throw EntityNotFoundException::set($command->setId);

        $set->assign(
            $command->weightGrams,
            $command->reps,
            $command->type,
            $command->restAfterSeconds,
        );

        $this->domainSession->flush();
    }
}
