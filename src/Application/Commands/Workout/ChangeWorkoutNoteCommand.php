<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

final readonly class ChangeWorkoutNoteCommand
{
    public function __construct(
        public string $userId,
        public string $workoutId,
        public ?string $note,
    ) {
    }
}
