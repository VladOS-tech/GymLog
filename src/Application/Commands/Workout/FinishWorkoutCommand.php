<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

final readonly class FinishWorkoutCommand
{
    public function __construct(
        public string $userId,
        public string $workoutId,
    ) {
    }
}
