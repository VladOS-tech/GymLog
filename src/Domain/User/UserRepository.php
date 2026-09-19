<?php

declare(strict_types=1);

namespace GymLog\Domain\User;

/**
 * Порт: что домену нужно от хранилища пользователей.
 *
 * Интерфейс живёт в домене, реализация — в PortAdapters/Secondary. Поэтому домен
 * ничего не знает ни про Doctrine, ни про SQL: зависимость направлена внутрь.
 */
interface UserRepository
{
    /**
     * Берёт пользователя под наблюдение. Запись в БД произойдёт на flush, который
     * зовёт слой Application: границу транзакции задаёт сценарий, а не репозиторий.
     */
    public function save(User $user): void;

    public function find(string $id): ?User;

    /**
     * Нужен на регистрации и логине. Регистр email нормализует домен (User::normalizeEmail),
     * поэтому сравнение здесь обычное, без LOWER().
     */
    public function findByEmail(string $email): ?User;
}
