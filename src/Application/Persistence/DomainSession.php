<?php

declare(strict_types=1);

namespace GymLog\Application\Persistence;

/**
 * Порт единицы работы: «вылей всё накопленное в БД одной транзакцией».
 *
 * Репозитории делают только persist/remove, а момент записи выбирает сценарий —
 * поэтому flush живёт здесь, а не внутри репозитория.
 */
interface DomainSession
{
    /**
     * @throws UniqueConstraintViolationException при нарушении уникального индекса
     */
    public function flush(): void;
}
