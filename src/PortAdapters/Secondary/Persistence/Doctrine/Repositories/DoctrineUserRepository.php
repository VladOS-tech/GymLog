<?php

declare(strict_types=1);

namespace GymLog\PortAdapters\Secondary\Persistence\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GymLog\Domain\User\User;
use GymLog\Domain\User\UserRepository;

/**
 * Адаптер порта UserRepository поверх Doctrine ORM.
 *
 * Префикс Doctrine в имени не декоративный: он оставляет место второй реализации
 * того же порта (например InMemoryUserRepository для тестов), и подмена сведётся
 * к одной строке в config/services/repositories.xml.
 */
final readonly class DoctrineUserRepository implements UserRepository
{
    /** @var EntityRepository<User> */
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    public function save(User $user): void
    {
        // Только persist, без flush: см. комментарий в порту.
        $this->entityManager->persist($user);
    }

    public function find(string $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        // Нормализуем так же, как конструктор User: в колонке лежит только нижний регистр,
        // и без этого поиск по "User@Mail.ru" не нашёл бы существующего "user@mail.ru".
        return $this->repository->findOneBy(['email' => User::normalizeEmail($email)]);
    }
}
