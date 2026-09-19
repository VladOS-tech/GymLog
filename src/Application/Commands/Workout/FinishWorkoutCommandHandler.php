<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Workout\WorkoutException;

final readonly class FinishWorkoutCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * Завершение — финальная точка: после него тренировка только читается.
     *
     * @throws EntityNotFoundException
     * @throws WorkoutException если тренировка уже завершена
     */
    public function handle(FinishWorkoutCommand $command): void
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $workout->finish();

        $this->domainSession->flush();
    }
}
