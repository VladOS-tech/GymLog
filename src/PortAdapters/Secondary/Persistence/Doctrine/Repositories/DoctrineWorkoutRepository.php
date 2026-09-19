<?php

declare(strict_types=1);

namespace GymLog\PortAdapters\Secondary\Persistence\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutRepository;

final readonly class DoctrineWorkoutRepository implements WorkoutRepository
{
    /** @var EntityRepository<Workout> */
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Workout::class);
    }

    public function save(Workout $workout): void
    {
        // Упражнения и подходы уедут сами: в маппинге у коллекций стоит cascade-persist.
        $this->entityManager->persist($workout);
    }

    public function remove(Workout $workout): void
    {
        $this->entityManager->remove($workout);
    }

    public function find(string $id): ?Workout
    {
        return $this->repository->find($id);
    }

    public function findActiveByUser(string $userId): ?Workout
    {
        // В условии по связи user допустимо передать сам идентификатор, а не сущность:
        // Doctrine подставит его в колонку user_id и лишнего SELECT'а не сделает.
        return $this->repository->findOneBy(['user' => $userId, 'finishedAt' => null]);
    }

    public function findLastFinishedByUser(string $userId): ?Workout
    {
        // findOneBy не умеет выразить IS NOT NULL, поэтому DQL.
        // Запрос ложится на частичный индекс workout_user_finished_at_index.
        return $this->entityManager->createQueryBuilder()
            ->select('w')
            ->from(Workout::class, 'w')
            ->where('w.user = :userId')
            ->andWhere('w.finishedAt IS NOT NULL')
            ->orderBy('w.finishedAt', 'DESC')
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
