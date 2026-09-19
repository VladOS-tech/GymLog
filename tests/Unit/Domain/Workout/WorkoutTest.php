<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Domain\Workout;

use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\User\User;
use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[CoversClass(Workout::class)]
final class WorkoutTest extends TestCase
{
    public function testNewWorkoutIsActive(): void
    {
        $workout = $this->makeWorkout();

        self::assertTrue($workout->isActive());
        self::assertNull($workout->getFinishedAt());
    }

    public function testFinishClosesWorkout(): void
    {
        $workout = $this->makeWorkout();
        $workout->finish();

        self::assertFalse($workout->isActive());
        self::assertNotNull($workout->getFinishedAt());
    }

    /** Завершение — финальная точка, повторно его не проводят. */
    public function testFinishingTwiceIsRejected(): void
    {
        $workout = $this->makeWorkout();
        $workout->finish();

        $this->expectException(WorkoutException::class);
        $workout->finish();
    }

    public function testFinishingBeforeStartIsRejected(): void
    {
        $workout = new Workout(Uuid::uuid7()->toString(), $this->makeUser(), new \DateTimeImmutable('-1 hour'));

        $this->expectException(WorkoutException::class);
        $workout->finish(new \DateTimeImmutable('-2 hours'));
    }

    /** Позиции раздаёт сам агрегат, клиент их не присылает. */
    public function testExercisesGetSequentialPositions(): void
    {
        $workout = $this->makeWorkout();

        $first = $workout->addExercise($this->makeExercise('Жим'));
        $second = $workout->addExercise($this->makeExercise('Присед'));
        $third = $workout->addExercise($this->makeExercise('Тяга'));

        self::assertSame([0, 1, 2], [$first->getPosition(), $second->getPosition(), $third->getPosition()]);
    }

    public function testAddingExerciseToFinishedWorkoutIsRejected(): void
    {
        $workout = $this->makeWorkout();
        $workout->finish();

        $this->expectException(WorkoutException::class);
        $workout->addExercise($this->makeExercise('Жим'));
    }

    public function testRemovingExerciseFromFinishedWorkoutIsRejected(): void
    {
        $workout = $this->makeWorkout();
        $workoutExercise = $workout->addExercise($this->makeExercise('Жим'));
        $workout->finish();

        $this->expectException(WorkoutException::class);
        $workout->removeExercise($workoutExercise);
    }

    public function testChangingNoteOnFinishedWorkoutIsRejected(): void
    {
        $workout = $this->makeWorkout();
        $workout->finish();

        $this->expectException(WorkoutException::class);
        $workout->changeNote('поздняя мысль');
    }

    private function makeWorkout(): Workout
    {
        return new Workout(Uuid::uuid7()->toString(), $this->makeUser());
    }

    private function makeUser(): User
    {
        return new User(Uuid::uuid7()->toString(), 'user@example.test', 'hash');
    }

    private function makeExercise(string $name): Exercise
    {
        return new Exercise(Uuid::uuid7()->toString(), $name);
    }
}
