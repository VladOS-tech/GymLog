<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\RemoveExerciseFromWorkoutCommand;
use GymLog\Application\Commands\Workout\RemoveExerciseFromWorkoutCommandHandler;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Domain\Workout\SetType;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(RemoveExerciseFromWorkoutCommandHandler::class)]
final class RemoveExerciseFromWorkoutCommandHandlerTest extends WorkoutCommandTestCase
{
    /** Удаление упражнения уносит его подходы — осознанное поведение для активной тренировки. */
    public function testRemovesExerciseWithItsSets(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());
        $workoutExercise->addSet(50_000, 10, SetType::Working);

        $this->handler()->handle(new RemoveExerciseFromWorkoutCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
        ));

        self::assertNull($workout->findExercise($workoutExercise->getId()));
        self::assertCount(0, $workout->getExercises());
        self::assertSame(1, $this->session->flushCount);
    }

    public function testUnknownWorkoutExerciseIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);

        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(new RemoveExerciseFromWorkoutCommand(
            $user->getId(),
            $workout->getId(),
            Uuid::uuid7()->toString(),
        ));
    }

    public function testFinishedWorkoutIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());
        $workout->finish();

        $this->expectException(WorkoutException::class);

        $this->handler()->handle(new RemoveExerciseFromWorkoutCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
        ));
    }

    private function handler(): RemoveExerciseFromWorkoutCommandHandler
    {
        return new RemoveExerciseFromWorkoutCommandHandler($this->ownedWorkouts, $this->session);
    }
}
