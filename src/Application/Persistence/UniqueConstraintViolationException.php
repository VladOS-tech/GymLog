<?php

declare(strict_types=1);

namespace GymLog\Application\Persistence;

/**
 * Нарушение уникальности, переведённое на язык прикладного слоя.
 *
 * Нужна своя: ловить в Application исключение DBAL значило бы протащить Doctrine
 * в слой сценариев, и вся возня с портами потеряла бы смысл. Перевод делает адаптер.
 */
class UniqueConstraintViolationException extends \RuntimeException
{
}
