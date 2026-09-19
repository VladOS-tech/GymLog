<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Fakes;

use GymLog\Domain\User\User;
use GymLog\Domain\User\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->getId()] = $user;
    }

    public function find(string $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        $normalized = User::normalizeEmail($email);

        foreach ($this->users as $user) {
            if ($user->getEmail() === $normalized) {
                return $user;
            }
        }

        return null;
    }
}
