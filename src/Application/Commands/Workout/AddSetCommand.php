<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Domain\Workout\SetType;

final readonly class AddSetCommand
{
    /**
     * Вес в граммах: конвертацию из килограммов делает примари-адаптер,
     * в приложение число приходит уже целым.
     */
    public function __construct(
        public string $userId,
        public string $workoutId,
        public string $workoutExerciseId,
        public int $weightGrams,
        public int $reps,
        public SetType $type,
        public ?int $restAfterSeconds = null,
    ) {
    }
}
