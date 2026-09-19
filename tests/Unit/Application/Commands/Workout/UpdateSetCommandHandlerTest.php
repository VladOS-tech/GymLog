<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\UpdateSetCommand;
use GymLog\Application\Commands\Workout\UpdateSetCommandHandler;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Domain\Workout\SetType;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(UpdateSetCommandHandler::class)]
final class UpdateSetCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testUpdatesSetOfActiveWorkout(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());
        $set = $workoutExercise->addSet(80_000, 8, SetType::Working);

        $this->handler()->handle(new UpdateSetCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
            $set->getId(),
            85_000,
            5,
            SetType::Warmup,
            90,
        ));

        self::assertSame(85_000, $set->getWeightGrams());
        self::assertSame(5, $set->getReps());
        self::assertSame(SetType::Warmup, $set->getType());
        self::assertSame(90, $set->getRestAfterSeconds());
        self::assertSame(1, $this->session->flushCount);
    }

    public function testUnknownSetIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());

        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(new UpdateSetCommand(
            $user->getId(),
            $workout->getId(),
            $workoutExercise->getId(),
            Uuid::uuid7()->toString(),
            85_000,
            5,
            SetType::Working,
        ));
    }

    /**
     * Ровно та дырка, которую мы закрыли в WorkoutSet::assign: без проверки внутри
     * подхода этот сценарий молча отредактировал бы завершённую тренировку.
     */
    public function testEditingSetOfFinishedWorkoutIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workoutExercise = $workout->addExercise($this->givenExercise());
        $set = $workoutExercise->addSet(80_000, 8, SetType::Working);
        $workout->finish();

        $this->expectException(WorkoutException::class);

        try {
            $this->handler()->handle(new UpdateSetCommand(
                $user->getId(),
                $workout->getId(),
                $workoutExercise->getId(),
                $set->getId(),
                85_000,
                5,
                SetType::Working,
            ));
        } finally {
            self::assertSame(80_000, $set->getWeightGrams(), 'Значения не должны были измениться');
            self::assertSame(0, $this->session->flushCount);
        }
    }

    private function handler(): UpdateSetCommandHandler
    {
        return new UpdateSetCommandHandler($this->ownedWorkouts, $this->session);
    }
}
