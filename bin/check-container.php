<?php

declare(strict_types=1);

/**
 * ВРЕМЕННЫЙ скрипт: проверяет, что контейнер собирает все сервисы прикладного слоя.
 * Юнит-тесты создают хендлеры руками, поэтому DI они не проверяют.
 *
 * Запуск: docker compose exec composer php bin/check-container.php
 */

$projectDir = dirname(__DIR__);

require_once $projectDir . '/vendor/autoload.php';

$container = (require $projectDir . '/config/container.php')($projectDir, 'dev', true);

$ids = [
    // порты -> адаптеры
    GymLog\Domain\User\UserRepository::class,
    GymLog\Domain\Exercise\ExerciseRepository::class,
    GymLog\Domain\Workout\WorkoutRepository::class,
    GymLog\Application\Persistence\DomainSession::class,
    GymLog\Application\Queries\LastResult\LastExerciseResultSearchService::class,
    // сценарии
    GymLog\Application\Commands\Workout\Services\OwnedWorkoutProvider::class,
    GymLog\Application\Commands\Workout\StartWorkoutCommandHandler::class,
    GymLog\Application\Commands\Workout\AddExerciseToWorkoutCommandHandler::class,
    GymLog\Application\Commands\Workout\RemoveExerciseFromWorkoutCommandHandler::class,
    GymLog\Application\Commands\Workout\ReorderWorkoutExercisesCommandHandler::class,
    GymLog\Application\Commands\Workout\AddSetCommandHandler::class,
    GymLog\Application\Commands\Workout\UpdateSetCommandHandler::class,
    GymLog\Application\Commands\Workout\ChangeWorkoutNoteCommandHandler::class,
    GymLog\Application\Commands\Workout\FinishWorkoutCommandHandler::class,
];

$failures = 0;

foreach ($ids as $id) {
    try {
        $service = $container->get($id);
        printf(" OK   %s -> %s\n", $id, $service::class);
    } catch (\Throwable $exception) {
        ++$failures;
        printf("FAIL  %s\n      %s\n", $id, $exception->getMessage());
    }
}

printf("\n%s\n", $failures === 0 ? 'Контейнер собирает всё.' : "Не собралось: {$failures}");

exit($failures === 0 ? 0 : 1);
