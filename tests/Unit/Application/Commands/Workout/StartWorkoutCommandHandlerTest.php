<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\StartWorkoutCommand;
use GymLog\Application\Commands\Workout\StartWorkoutCommandHandler;
use GymLog\Application\Exceptions\ActiveWorkoutAlreadyExistsException;
use GymLog\Application\Exceptions\EntityNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

#[CoversClass(StartWorkoutCommandHandler::class)]
final class StartWorkoutCommandHandlerTest extends WorkoutCommandTestCase
{
    public function testStartsWorkoutAndReturnsItsId(): void
    {
        $user = $this->givenUser();

        $workoutId = $this->handler()->handle(new StartWorkoutCommand($user->getId()));

        $workout = $this->workouts->find($workoutId);
        self::assertNotNull($workout);
        self::assertTrue($workout->isActive());
        self::assertSame(1, $this->session->flushCount);
    }

    public function testUnknownUserIsRejected(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $this->handler()->handle(new StartWorkoutCommand(Uuid::uuid7()->toString()));
    }

    /** Инвариант спеки, раздел 3.2: вторая активная тренировка не создаётся. */
    public function testSecondActiveWorkoutIsRejected(): void
    {
        $user = $this->givenUser();
        $this->givenActiveWorkout($user);

        $this->expectException(ActiveWorkoutAlreadyExistsException::class);

        try {
            $this->handler()->handle(new StartWorkoutCommand($user->getId()));
        } finally {
            // Проверку прошли до записи, значит в БД никто не ходил.
            self::assertSame(0, $this->session->flushCount);
        }
    }

    /**
     * Гонка: проверка «активной нет» прошла, но между ней и вставкой кто-то успел
     * создать тренировку, и уникальный индекс отверг запись. Наружу должна уехать
     * та же доменная ошибка, а не сырое нарушение констрейнта.
     */
    public function testUniqueViolationOnFlushBecomesSameDomainError(): void
    {
        $user = $this->givenUser();
        $this->session->failNextFlushWithUniqueViolation();

        $this->expectException(ActiveWorkoutAlreadyExistsException::class);

        $this->handler()->handle(new StartWorkoutCommand($user->getId()));
    }

    private function handler(): StartWorkoutCommandHandler
    {
        return new StartWorkoutCommandHandler($this->users, $this->workouts, $this->session);
    }
}
