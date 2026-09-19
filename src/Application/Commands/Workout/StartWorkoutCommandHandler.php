<?php

declare(strict_types=1);

namespace GymLog\Application\Commands\Workout;

use GymLog\Application\Exceptions\ActiveWorkoutAlreadyExistsException;
use GymLog\Application\Exceptions\EntityNotFoundException;
use GymLog\Application\Persistence\DomainSession;
use GymLog\Application\Persistence\UniqueConstraintViolationException;
use GymLog\Domain\User\UserRepository;
use GymLog\Domain\Workout\Workout;
use GymLog\Domain\Workout\WorkoutRepository;
use Ramsey\Uuid\Uuid;

final readonly class StartWorkoutCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private WorkoutRepository $workoutRepository,
        private DomainSession $domainSession,
    ) {
    }

    /**
     * @return string id созданной тренировки
     *
     * @throws EntityNotFoundException
     * @throws ActiveWorkoutAlreadyExistsException
     */
    public function handle(StartWorkoutCommand $command): string
    {
        $user = $this->userRepository->find($command->userId)
            ?? throw EntityNotFoundException::user($command->userId);

        // Проверка ради внятной ошибки: пользователь должен получить «у тебя уже есть
        // активная тренировка», а не пятисотку с текстом про индекс.
        if ($this->workoutRepository->findActiveByUser($command->userId) !== null) {
            throw ActiveWorkoutAlreadyExistsException::forUser($command->userId);
        }

        $workout = new Workout(Uuid::uuid7()->toString(), $user);
        $this->workoutRepository->save($workout);

        try {
            $this->domainSession->flush();
        } catch (UniqueConstraintViolationException) {
            // Между SELECT выше и INSERT здесь есть окно: два одновременных запроса
            // (хотя бы двойной тап по кнопке) оба пройдут проверку. Настоящую гарантию
            // даёт частичный уникальный индекс workout_active_per_user_uniq_index,
            // а мы переводим его отказ в ту же самую доменную ошибку.
            throw ActiveWorkoutAlreadyExistsException::forUser($command->userId);
        }

        return $workout->getId();
    }
}
