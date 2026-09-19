<?php

declare(strict_types=1);

namespace GymLog\Domain\Workout;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GymLog\Domain\Exercise\Exercise;
use Ramsey\Uuid\Uuid;

class WorkoutExercise
{
    private string $id;
    private Workout $workout;
    private Exercise $exercise;

    /** Порядок упражнения в тренировке. В отличие от подходов, упражнения пользователь переупорядочивает. */
    private int $position;

    private ?string $note;

    /** Отдых до следующего упражнения, секунды. */
    private ?int $restAfterExerciseSeconds;

    /** @var Collection<int, WorkoutSet> */
    private Collection $sets;

    public function __construct(string $id, Workout $workout, Exercise $exercise, int $position)
    {
        if ($position < 0) {
            throw new \InvalidArgumentException('Позиция упражнения не может быть отрицательной.');
        }

        $this->id = $id;
        $this->workout = $workout;
        $this->exercise = $exercise;
        $this->position = $position;
        $this->note = null;
        $this->restAfterExerciseSeconds = null;
        $this->sets = new ArrayCollection();
    }

    public function addSet(int $weightGrams, int $reps, SetType $type, ?int $restAfterSeconds = null): WorkoutSet
    {
        $this->workout->assertEditable();

        // UUIDv7 монотонен, поэтому id одновременно задаёт порядок подхода — отдельной колонки не нужно.
        $set = new WorkoutSet(Uuid::uuid7()->toString(), $this, $weightGrams, $reps, $type, $restAfterSeconds);
        $this->sets->add($set);

        return $set;
    }

    public function changePosition(int $position): void
    {
        $this->workout->assertEditable();

        if ($position < 0) {
            throw new \InvalidArgumentException('Позиция упражнения не может быть отрицательной.');
        }

        $this->position = $position;
    }

    public function changeNote(?string $note): void
    {
        $this->workout->assertEditable();

        $this->note = $note;
    }

    public function changeRestAfterExerciseSeconds(?int $seconds): void
    {
        $this->workout->assertEditable();

        if ($seconds !== null && $seconds < 0) {
            throw new \InvalidArgumentException('Отдых не может быть отрицательным.');
        }

        $this->restAfterExerciseSeconds = $seconds;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWorkout(): Workout
    {
        return $this->workout;
    }

    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getRestAfterExerciseSeconds(): ?int
    {
        return $this->restAfterExerciseSeconds;
    }

    /**
     * Подходы в порядке создания: ORDER BY id задан в маппинге, а id — монотонный UUIDv7.
     *
     * @return Collection<int, WorkoutSet>
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }
}