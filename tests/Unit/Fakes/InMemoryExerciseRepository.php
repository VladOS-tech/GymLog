<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Fakes;

use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\Exercise\ExerciseRepository;

final class InMemoryExerciseRepository implements ExerciseRepository
{
    /** @var array<string, Exercise> */
    private array $exercises = [];

    public function save(Exercise $exercise): void
    {
        $this->exercises[$exercise->getId()] = $exercise;
    }

    public function find(string $id): ?Exercise
    {
        return $this->exercises[$id] ?? null;
    }

    public function findByName(string $name): ?Exercise
    {
        foreach ($this->exercises as $exercise) {
            if ($exercise->getName() === $name) {
                return $exercise;
            }
        }

        return null;
    }

    public function findActive(): array
    {
        $active = array_values(array_filter(
            $this->exercises,
            static fn (Exercise $exercise): bool => !$exercise->isArchived(),
        ));

        usort($active, static fn (Exercise $a, Exercise $b): int => $a->getName() <=> $b->getName());

        return $active;
    }
}
