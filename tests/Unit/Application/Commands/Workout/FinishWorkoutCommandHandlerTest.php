<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\FinishWorkoutCommand;
use GymLog\Application\Commands\Workout\FinishWorkoutCommandHandler;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(FinishWorkoutCommandHandler::class)]
final class FinishWorkoutCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testFinishesWorkout(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);

        $this->handler()->handle(new FinishWorkoutCommand($user->getId(), $workout->getId()));

        self::assertFalse($workout->isActive());
        self::assertSame(1, $this->session->flushCount);
    }

    public function testFinishingTwiceIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $workout->finish();

        $this->expectException(WorkoutException::class);

        $this->handler()->handle(new FinishWorkoutCommand($user->getId(), $workout->getId()));
    }

    public function testUnknownWorkoutIsRejected(): void
    {
        $user = $this->givenUser();

        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(new FinishWorkoutCommand($user->getId(), Uuid::uuid7()->toString()));
    }

    private function handler(): FinishWorkoutCommandHandler
    {
        return new FinishWorkoutCommandHandler($this->ownedWorkouts, $this->session);
    }
}
