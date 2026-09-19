<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Fakes;

use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutRepository;

final class InMemoryWorkoutRepository implements WorkoutRepository
{
    /** @var array<string, Workout> */
    private array $workouts = [];

    public function save(Workout $workout): void
    {
        $this->workouts[$workout->getId()] = $workout;
    }

    public function remove(Workout $workout): void
    {
        unset($this->workouts[$workout->getId()]);
    }

    public function find(string $id): ?Workout
    {
        return $this->workouts[$id] ?? null;
    }

    public function findActiveByUser(string $userId): ?Workout
    {
        foreach ($this->workouts as $workout) {
            if ($workout->getUser()->getId() === $userId && $workout->isActive()) {
                return $workout;
            }
        }

        return null;
    }

    public function findLastFinishedByUser(string $userId): ?Workout
    {
        $finished = array_filter(
            $this->workouts,
            static fn (Workout $workout): bool => $workout->getUser()->getId() === $userId && !$workout->isActive(),
        );

        usort(
            $finished,
            static fn (Workout $a, Workout $b): int => $b->getFinishedAt() <=> $a->getFinishedAt(),
        );

        return $finished[0] ?? null;
    }
}
