<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Workout\WorkoutException;

final readonly class ChangeWorkoutNoteCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     * @throws WorkoutException если тренировка уже завершена
     */
    public function handle(ChangeWorkoutNoteCommand $command): void
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $workout->changeNote($command->note);

        $this->domainSession->flush();
    }
}
