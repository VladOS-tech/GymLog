<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Domain\Exercise;

use GymLog\Domain\Exercise\Exercise;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[CoversClass(Exercise::class)]
final class ExerciseTest extends TestCase
{
    public function testNewExerciseIsNotArchived(): void
    {
        self::assertFalse($this->makeExercise()->isArchived());
    }

    /**
     * Удаление упражнения только мягкое: оно переиспользуется между тренировками,
     * и физическое удаление порвало бы историю (спека, раздел 6).
     */
    public function testArchiveAndRestore(): void
    {
        $exercise = $this->makeExercise();

        $exercise->archive();
        self::assertTrue($exercise->isArchived());

        $exercise->restore();
        self::assertFalse($exercise->isArchived());
    }

    private function makeExercise(): Exercise
    {
        return new Exercise(Uuid::uuid7()->toString(), 'Жим лёжа');
    }
}
