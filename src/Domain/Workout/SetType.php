<?php

declare(strict_types=1);

namespace GymLog\Domain\Workout;

/**
 * Тип подхода. Именно enum, а не bool: новые значения (dropset, failure) добавляются
 * сюда без миграции структуры — колонка остаётся VARCHAR(32).
 *
 * При добавлении значения не забыть расширить CHECK-констрейнт workout_set_type_check
 * отдельной миграцией.
 */
enum SetType: string
{
    case Warmup = 'warmup';
    case Working = 'working';
}