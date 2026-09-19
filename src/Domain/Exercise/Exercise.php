<?php

declare(strict_types=1);

namespace GymLog\Domain\Exercise;

class Exercise
{
    private string $id;
    private string $name;
    private bool $isArchived;
    private \DateTimeImmutable $createdAt;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isArchived = false;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Мягкое удаление: упражнение переиспользуется историей тренировок и физически не удаляется.
     * На уровне БД это подстраховано FK workout_exercise -> exercise с ON DELETE RESTRICT.
     */
    public function archive(): void
    {
        $this->isArchived = true;
    }

    public function restore(): void
    {
        $this->isArchived = false;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}