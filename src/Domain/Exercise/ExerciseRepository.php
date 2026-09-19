<?php

declare(strict_types=1);

namespace GymLog\Domain\Exercise;

/**
 * Порт хранилища справочника упражнений.
 */
interface ExerciseRepository
{
    public function save(Exercise $exercise): void;

    public function find(string $id): ?Exercise;

    /**
     * Проверка уникальности имени перед созданием — уникальный индекс в БД оставляем
     * последним рубежом, а не способом сообщать пользователю об ошибке.
     */
    public function findByName(string $name): ?Exercise;

    /**
     * Список для экрана выбора упражнения: архивные не показываем (спека, раздел 6).
     *
     * Здесь сущности, а не read-модель: Exercise — плоский справочник из четырёх полей
     * без вложенных коллекций, грузить его целиком ничего не стоит.
     *
     * @return Exercise[]
     */
    public function findActive(): array;
}
