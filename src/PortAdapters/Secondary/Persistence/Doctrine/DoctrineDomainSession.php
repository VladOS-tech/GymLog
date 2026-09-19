<?php

declare(strict_types=1);

namespace GymLog\PortAdapters\Secondary\Persistence\Doctrine;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException as DbalUniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Application\Persistence\UniqueConstraintViolationException;

final readonly class DoctrineDomainSession implements DomainSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function flush(): void
    {
        try {
            $this->entityManager->flush();
        } catch (DbalUniqueConstraintViolationException $exception) {
            // Здесь и проходит граница: дальше наверх уезжает исключение прикладного слоя,
            // и сценарий не знает ни про DBAL, ни про имя индекса.
            //
            // Важно: после упавшего flush Doctrine закрывает EntityManager, и работать
            // с ним дальше нельзя. Для HTTP-запроса это нормально — процесс обработки
            // на этом и заканчивается.
            throw new UniqueConstraintViolationException($exception->getMessage(), previous: $exception);
        }
    }
}
