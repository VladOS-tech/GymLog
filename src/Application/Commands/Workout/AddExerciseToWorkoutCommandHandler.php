<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Exceptions\ExerciseArchivedException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Domain\Exercise\ExerciseRepository;
use GymLog\Domain\Workout\WorkoutException;

final readonly class AddExerciseToWorkoutCommandHandler
{
    public function __construct(
        private OwnedWorkoutProvider $ownedWorkoutProvider,
        private ExerciseRepository $exerciseRepository,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * @return string id созданного WorkoutExercise
     *
     * @throws EntityNotFoundException
     * @throws ExerciseArchivedException
     * @throws WorkoutException если тренировка уже завершена
     */
    public function handle(AddExerciseToWorkoutCommand $command): string
    {
        $workout = $this->ownedWorkoutProvider->get($command->workoutId, $command->userId);

        $exercise = $this->exerciseRepository->find($command->exerciseId)
            ?? throw EntityNotFoundException::exercise($command->exerciseId);

        if ($exercise->isArchived()) {
            throw ExerciseArchivedException::forExercise($command->exerciseId);
        }

        // Позицию раздаёт агрегат, проверку «тренировка активна» делает он же.
        $workoutExercise = $workout->addExercise($exercise);

        $this->domainSession->flush();

        return $workoutExercise->getId();
    }
}
