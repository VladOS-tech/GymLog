<?php

declare(strict_types=1);

namespace GymLog\PortAdapters\Secondary\Persistence\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GymLog\Domain\Exercise\Exercise;
use GymLog\Domain\Exercise\ExerciseRepository;

final readonly class DoctrineExerciseRepository implements ExerciseRepository
{
    /** @var EntityRepository<Exercise> */
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Exercise::class);
    }

    public function save(Exercise $exercise): void
    {
        $this->entityManager->persist($exercise);
    }

    public function find(string $id): ?Exercise
    {
        return $this->repository->find($id);
    }

    public function findByName(string $name): ?Exercise
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    public function findActive(): array
    {
        // Ложится на частичный индекс exercise_active_name_index (name) WHERE is_archived = false.
        return $this->repository->findBy(['isArchived' => false], ['name' => 'ASC']);
    }
}
