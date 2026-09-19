<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Workout\WorkoutException;

final readonly class RemoveExerciseFromWorkoutCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * Жёсткое удаление вместе с подходами (спека, раздел 6): в маппинге orphan-removal,
     * в БД ON DELETE CASCADE.
     *
     * @throws EntityNotFoundException
     * @throws WorkoutException если тренировка уже завершена
     */
    public function handle(RemoveExerciseFromWorkoutCommand $command): void
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $workoutExercise = $workout->findExercise($command->workoutExerciseId)
            ?? throw EntityNotFoundException::workoutExercise($command->workoutExerciseId);

        $workout->removeExercise($workoutExercise);

        $this->domainSession->flush();
    }
}
