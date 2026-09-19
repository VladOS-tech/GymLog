<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Domain\Workout;

use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\User\User;
use GymLog\Domain\Workout\SetType;
use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutException;
use GymLog\Domain\Workout\WorkoutExercise;
use GymLog\Domain\Workout\WorkoutSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[CoversClass(WorkoutSet::class)]
#[CoversClass(WorkoutExercise::class)]
final class WorkoutSetTest extends TestCase
{
    public function testAddSetStoresValues(): void
    {
        $set = $this->makeExerciseInActiveWorkout()->addSet(82_500, 6, SetType::Working, 120);

        self::assertSame(82_500, $set->getWeightGrams());
        self::assertSame(6, $set->getReps());
        self::assertSame(SetType::Working, $set->getType());
        self::assertSame(120, $set->getRestAfterSeconds());
    }

    /** Ноль — это вес собственного тела (подтягивания, отжимания), он допустим. */
    public function testZeroWeightIsAllowed(): void
    {
        $set = $this->makeExerciseInActiveWorkout()->addSet(0, 12, SetType::Working);

        self::assertSame(0, $set->getWeightGrams());
    }

    public function testNegativeWeightIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->makeExerciseInActiveWorkout()->addSet(-1, 10, SetType::Working);
    }

    public function testZeroRepsIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->makeExerciseInActiveWorkout()->addSet(50_000, 0, SetType::Working);
    }

    public function testNegativeRestIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->makeExerciseInActiveWorkout()->addSet(50_000, 10, SetType::Working, -1);
    }

    public function testAddingSetToFinishedWorkoutIsRejected(): void
    {
        $workoutExercise = $this->makeExerciseInActiveWorkout();
        $workoutExercise->getWorkout()->finish();

        $this->expectException(WorkoutException::class);
        $workoutExercise->addSet(50_000, 10, SetType::Working);
    }

    /**
     * Завершённая тренировка иммутабельна (спека, раздел 6). Проверка живёт в самом
     * WorkoutSet::assign, а не только в addSet: редактирование подхода придёт сюда напрямую.
     */
    public function testEditingSetOfFinishedWorkoutIsRejected(): void
    {
        $workoutExercise = $this->makeExerciseInActiveWorkout();
        $set = $workoutExercise->addSet(80_000, 8, SetType::Working);
        $workoutExercise->getWorkout()->finish();

        $this->expectException(WorkoutException::class);
        $set->assign(85_000, 8, SetType::Working, null);
    }

    public function testEditingSetOfActiveWorkoutIsAllowed(): void
    {
        $set = $this->makeExerciseInActiveWorkout()->addSet(80_000, 8, SetType::Working);

        $set->assign(85_000, 5, SetType::Warmup, 90);

        self::assertSame(85_000, $set->getWeightGrams());
        self::assertSame(5, $set->getReps());
        self::assertSame(SetType::Warmup, $set->getType());
    }

    /**
     * Страховка на документированное решение: у подхода нет колонки position, порядок
     * задаёт сам PK. Это работает, только пока id монотонно возрастает во времени —
     * то есть пока это UUIDv7 от ramsey/uuid. Смена генератора сломает порядок подходов
     * молча, и поймать это должен именно такой тест.
     */
    public function testSetIdsGrowMonotonically(): void
    {
        $workoutExercise = $this->makeExerciseInActiveWorkout();

        $previous = '';
        for ($i = 0; $i < 500; ++$i) {
            $id = $workoutExercise->addSet(50_000, 10, SetType::Working)->getId();
            self::assertGreaterThan($previous, $id, "Подход №{$i} получил id не больше предыдущего");
            $previous = $id;
        }
    }

    private function makeExerciseInActiveWorkout(): WorkoutExercise
    {
        $user = new User(Uuid::uuid7()->toString(), 'user@example.test', 'hash');
        $workout = new Workout(Uuid::uuid7()->toString(), $user);

        return $workout->addExercise(new Exercise(Uuid::uuid7()->toString(), 'Жим лёжа'));
    }
}
