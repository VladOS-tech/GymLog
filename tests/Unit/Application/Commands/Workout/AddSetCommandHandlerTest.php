<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\AddSetCommand;
use GymLog\Application\Commands\Workout\AddSetCommandHandler;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Domain\Workout\SetType;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(AddSetCommandHandler::class)]
final class AddSetCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testAddsSetAndReturnsItsId(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());

        $setId = $this->handler()->handle(new AddSetCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
            82_500,
            6,
            SetType::Working,
            120,
        ));

        $set = $workoutExercise->findSet($setId);
        self::assertNotNull($set);
        self::assertSame(82_500, $set->getWeightGrams());
        self::assertSame(SetType::Working, $set->getType());
        self::assertSame(1, $this->session->flushCount);
    }

    public function testUnknownWorkoutExerciseIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);

        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(new AddSetCommand(
            $user->getId(),
            $workout->getId(),
            Uuid::uuid7()->toString(),
            50_000,
            10,
            SetType::Working,
        ));
    }

    public function testFinishedWorkoutIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());
        $workout->finish();

        $this->expectException(WorkoutException::class);

        $this->handler()->handle(new AddSetCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
            50_000,
            10,
            SetType::Working,
        ));
    }

    public function testInvalidRepsAreRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());

        $this->expectException(\InvalidArgumentException::class);

        $this->handler()->handle(new AddSetCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
            50_000,
            0,
            SetType::Working,
        ));
    }

    private function handler(): AddSetCommandHandler
    {
        return new AddSetCommandHandler($this->ownedWorkouts, $this->session);
    }
}
