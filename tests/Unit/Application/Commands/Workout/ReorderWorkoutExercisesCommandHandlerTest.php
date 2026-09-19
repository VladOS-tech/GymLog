<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\ReorderWorkoutExercisesCommand;
use GymLog\Application\Commands\Workout\ReorderWorkoutExercisesCommandHandler;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReorderWorkoutExercisesCommandHandler::class)]
final class ReorderWorkoutExercisesCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testReordersExercises(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $first = $workout->addExercise($this->givenExercise('Жим'));
        $second = $workout->addExercise($this->givenExercise('Присед'));
        $third = $workout->addExercise($this->givenExercise('Тяга'));

        $this->handler()->handle(new ReorderWorkoutExercisesCommand(
            $user->getId(),
            $workout->getId(),
            [$third->getId(), $first->getId(), $second->getId()],
        ));

        self::assertSame(0, $third->getPosition());
        self::assertSame(1, $first->getPosition());
        self::assertSame(2, $second->getPosition());
        self::assertSame(1, $this->session->flushCount);
    }

    /** Неполный список — ошибка: иначе часть упражнений осталась бы со старыми позициями. */
    public function testPartialOrderIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $first = $workout->addExercise($this->givenExercise('Жим'));
        $workout->addExercise($this->givenExercise('Присед'));

        $this->expectException(WorkoutException::class);

        $this->handler()->handle(new ReorderWorkoutExercisesCommand(
            $user->getId(),
            $workout->getId(),
            [$first->getId()],
        ));
    }

    private function handler(): ReorderWorkoutExercisesCommandHandler
    {
        return new ReorderWorkoutExercisesCommandHandler($this->ownedWorkouts, $this->session);
    }
}
