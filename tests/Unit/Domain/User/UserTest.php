<?php

declare(strict_types=1);

namespace GymLog\Test\Unit\Domain\User;

use GymLog\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    /**
     * Уникальность email держит обычный UNIQUE-индекс по колонке, без LOWER(),
     * поэтому нормализовать обязан домен — иначе в базу попадут два «разных» адреса.
     */
    public function testEmailIsNormalizedOnConstruction(): void
    {
        $user = new User(Uuid::uuid7()->toString(), '  User@Mail.RU ', 'hash');

        self::assertSame('user@mail.ru', $user->getEmail());
    }

    public function testNormalizeEmailTrimsAndLowercases(): void
    {
        self::assertSame('a@b.c', User::normalizeEmail(" A@B.C\n"));
    }
}
