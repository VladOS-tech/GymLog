<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

final readonly class ReorderWorkoutExercisesCommand
{
    /**
     * @param string[] $orderedWorkoutExerciseIds полный список id в новом порядке
     */
    public function __construct(
        public string $userId,
        public string $workoutId,
        public array $orderedWorkoutExerciseIds,
    ) {
    }
}
