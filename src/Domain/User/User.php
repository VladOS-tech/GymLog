<?php

declare(strict_types=1);

namespace GymLog\Domain\User;

class User
{
    private string $id;
    private string $email;
    private string $passwordHash;
    private \DateTimeImmutable $createdAt;

    public function __construct(string $id, string $email, string $passwordHash)
    {
        $this->id = $id;
        $this->email = self::normalizeEmail($email);
        $this->passwordHash = $passwordHash;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Уникальность email в БД — обычный UNIQUE-индекс по колонке, поэтому регистр
     * нормализуется здесь: в БД никогда не попадает "User@Mail.ru" рядом с "user@mail.ru".
     */
    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}