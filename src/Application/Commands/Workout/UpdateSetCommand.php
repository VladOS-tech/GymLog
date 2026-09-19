<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Domain\Workout\SetType;

final readonly class UpdateSetCommand
{
    public function __construct(
        public string $userId,
        public string $workoutId,
        public string $workoutExerciseId,
        public string $setId,
        public int $weightGrams,
        public int $reps,
        public SetType $type,
        public ?int $restAfterSeconds = null,
    ) {
    }
}
