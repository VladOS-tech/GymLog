<?php

declare(strict_types=1);

namespace GymLog\Domain\Workout;

/**
 * Подход.
 *
 * Поля position нет намеренно (см. спеку, раздел 5): порядок подходов = порядок их создания
 * и пользователем не меняется. Порядок обеспечивается самим PK — это UUIDv7, который
 * монотонно возрастает во времени, поэтому ORDER BY id даёт порядок создания.
 */
class WorkoutSet
{
    private string $id;
    private WorkoutExercise $workoutExercise;

    /** Вес в граммах: целое, без плавающей точки. 82.5 кг -> 82500. */
    private int $weightGrams;

    private int $reps;
    private SetType $type;

    /** Отдых до следующего подхода, секунды. */
    private ?int $restAfterSeconds;

    public function __construct(
        string $id,
        WorkoutExercise $workoutExercise,
        int $weightGrams,
        int $reps,
        SetType $type,
        ?int $restAfterSeconds = null,
    ) {
        $this->id = $id;
        $this->workoutExercise = $workoutExercise;

        $this->assign($weightGrams, $reps, $type, $restAfterSeconds);
    }

    public function assign(int $weightGrams, int $reps, SetType $type, ?int $restAfterSeconds): void
    {
        if ($weightGrams < 0) {
            throw new \InvalidArgumentException('Вес не может быть отрицательным.');
        }

        if ($reps < 1) {
            throw new \InvalidArgumentException('В подходе должно быть хотя бы одно повторение.');
        }

        if ($restAfterSeconds !== null && $restAfterSeconds < 0) {
            throw new \InvalidArgumentException('Отдых не может быть отрицательным.');
        }

        $this->weightGrams = $weightGrams;
        $this->reps = $reps;
        $this->type = $type;
        $this->restAfterSeconds = $restAfterSeconds;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWorkoutExercise(): WorkoutExercise
    {
        return $this->workoutExercise;
    }

    public function getWeightGrams(): int
    {
        return $this->weightGrams;
    }

    public function getReps(): int
    {
        return $this->reps;
    }

    public function getType(): SetType
    {
        return $this->type;
    }

    public function getRestAfterSeconds(): ?int
    {
        return $this->restAfterSeconds;
    }
}