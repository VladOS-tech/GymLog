<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Application\Commands\Workout;

use GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider;
use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\User\User;
use GymLog\Domain\Workout\Workout;
use GymLog\Test\Unit\Fakes\FakeDomainSession;
use GymLog\Test\Unit\Fakes\InMemoryExerciseRepository;
use GymLog\Test\Unit\Fakes\InMemoryUserRepository;
use GymLog\Test\Unit\Fakes\InMemoryWorkoutRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Общая обвязка для тестов сценариев.
 *
 * Ни базы, ни Doctrine здесь нет: хендлеры зависят только от портов, поэтому
 * вместо адаптеров подставляются реализации в памяти. Это и есть практическая
 * польза от того, что интерфейсы живут в домене, а не снаружи.
 */
abstract class WorkoutCommandTestCase extends TestCase
{
    protected InMemoryUserRepository $users;
    protected InMemoryExerciseRepository $exercises;
    protected InMemoryWorkoutRepository $workouts;
    protected FakeDomainSession $session;
    protected OwnedWorkoutProvider $ownedWorkouts;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->exercises = new InMemoryExerciseRepository();
        $this->workouts = new InMemoryWorkoutRepository();
        $this->session = new FakeDomainSession();
        $this->ownedWorkouts = new OwnedWorkoutProvider($this->workouts);
    }

    protected function givenUser(): User
    {
        $user = new User(Uuid::uuid7()->toString(), Uuid::uuid7()->toString() . '@example.test', 'hash');
        $this->users->save($user);

        return $user;
    }

    protected function givenExercise(string $name = 'Жим лёжа'): Exercise
    {
        $exercise = new Exercise(Uuid::uuid7()->toString(), $name);
        $this->exercises->save($exercise);

        return $exercise;
    }

    protected function givenActiveWorkout(User $user): Workout
    {
        $workout = new Workout(Uuid::uuid7()->toString(), $user);
        $this->workouts->save($workout);

        return $workout;
    }
}
