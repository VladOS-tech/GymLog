<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\AddExerciseToWorkoutCommand;
use GymLog\Application\Commands\Workout\AddExerciseToWorkoutCommandHandler;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Exceptions\ExerciseArchivedException;
use GymLog\Application\Exceptions\WorkoutAccessDeniedException;
use GymLog\Domain\Workout\WorkoutException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(AddExerciseToWorkoutCommandHandler::class)]
final class AddExerciseToWorkoutCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testAddsExerciseAndReturnsItsId(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $exercise = $this->givenExercise();

        $id = $this->handler()->handle(
            new AddExerciseToWorkoutCommand($user->getId(), $workout->getId(), $exercise->getId()),
        );

        self::assertNotNull($workout->findExercise($id));
        self::assertSame(0, $workout->findExercise($id)->getPosition());
        self::assertSame(1, $this->session->flushCount);
    }

    /** id тренировки приходит от клиента, поэтому владельца проверяем всегда. */
    public function testAnotherUsersWorkoutIsRejected(): void
    {
        $owner = $this->givenUser();
        $stranger = $this->givenUser();
        $workout = $this->givenActiveWorkout($owner);
        $exercise = $this->givenExercise();

        $this->expectException(WorkoutAccessDeniedException::class);

        $this->handler()->handle(
            new AddExerciseToWorkoutCommand($stranger->getId(), $workout->getId(), $exercise->getId()),
        );
    }

    public function testUnknownExerciseIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);

        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(
            new AddExerciseToWorkoutCommand($user->getId(), $workout->getId(), Uuid::uuid7()->toString()),
        );
    }

    /** Архивное упражнение убрано из списка выбора (спека, раздел 6). */
    public function testArchivedExerciseIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $exercise = $this->givenExercise();
        $exercise->archive();

        $this->expectException(ExerciseArchivedException::class);

        $this->handler()->handle(
            new AddExerciseToWorkoutCommand($user->getId(), $workout->getId(), $exercise->getId()),
        );
    }

    public function testFinishedWorkoutIsRejected(): void
    {
        $user = $this->givenUser();
        $workout = $this->givenActiveWorkout($user);
        $exercise = $this->givenExercise();
        $workout->finish();

        $this->expectException(WorkoutException::class);

        $this->handler()->handle(
            new AddExerciseToWorkoutCommand($user->getId(), $workout->getId(), $exercise->getId()),
        );
    }

    private function handler(): AddExerciseToWorkoutCommandHandler
    {
        return new AddExerciseToWorkoutCommandHandler($this->ownedWorkouts, $this->exercises, $this->session);
    }
}
