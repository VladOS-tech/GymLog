<?php

declare(strict_types=1);

namespace GymLog\Domain\Workout;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\User\User;
use Ramsey\Uuid\Uuid;

/**
 * Корень агрегата «тренировка».
 *
 * Активная тренировка — та, у которой finishedAt IS NULL; отдельного флага нет.
 * Инвариант «у пользователя одна активная тренировка» физически держит частичный
 * уникальный индекс workout_active_per_user_uniq_index.
 */
class Workout
{
    private string $id;
    private User $user;
    private \DateTimeImmutable $startedAt;
    private ?\DateTimeImmutable $finishedAt;
    private ?string $note;

    /** @var Collection<int, WorkoutExercise> */
    private Collection $exercises;

    public function __construct(string $id, User $user, ?\DateTimeImmutable $startedAt = null)
    {
        $this->id = $id;
        $this->user = $user;
        $this->startedAt = $startedAt ?? new \DateTimeImmutable();
        $this->finishedAt = null;
        $this->note = null;
        $this->exercises = new ArrayCollection();
    }

    /**
     * Завершение тренировки — финальная точка: дальше она read-only.
     */
    public function finish(?\DateTimeImmutable $finishedAt = null): void
    {
        $this->assertEditable();

        $finishedAt ??= new \DateTimeImmutable();

        if ($finishedAt < $this->startedAt) {
            throw new WorkoutException('Тренировка не может завершиться раньше, чем началась.');
        }

        $this->finishedAt = $finishedAt;
    }

    public function isActive(): bool
    {
        return $this->finishedAt === null;
    }

    /**
     * @throws WorkoutException если тренировка уже завершена
     */
    public function assertEditable(): void
    {
        if (!$this->isActive()) {
            throw new WorkoutException('Завершённая тренировка неизменяема.');
        }
    }

    public function addExercise(Exercise $exercise): WorkoutExercise
    {
        $this->assertEditable();

        $workoutExercise = new WorkoutExercise(
            Uuid::uuid7()->toString(),
            $this,
            $exercise,
            $this->nextPosition(),
        );
        $this->exercises->add($workoutExercise);

        return $workoutExercise;
    }

    /**
     * Жёсткое удаление упражнения вместе с подходами — только внутри активной тренировки.
     * orphanRemoval в маппинге + ON DELETE CASCADE в БД.
     */
    public function removeExercise(WorkoutExercise $workoutExercise): void
    {
        $this->assertEditable();

        $this->exercises->removeElement($workoutExercise);
    }

    public function changeNote(?string $note): void
    {
        $this->assertEditable();

        $this->note = $note;
    }

    private function nextPosition(): int
    {
        $positions = $this->exercises->map(
            static fn (WorkoutExercise $workoutExercise): int => $workoutExercise->getPosition(),
        )->toArray();

        return $positions === [] ? 0 : max($positions) + 1;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @return Collection<int, WorkoutExercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }
}