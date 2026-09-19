<?php

declare(strict_types=1);

namespace GymLog\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Начальная схема Gym Log: users, exercise, workout, workout_exercise, workout_set.
 */
final class Version20260815160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Начальная схема Gym Log MVP';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on \'postgresql\'.',
        );

        // users — "user" зарезервировано в PostgreSQL.
        $this->addSql('CREATE TABLE users (id VARCHAR(64) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        // Регистр email нормализуется в домене (User::normalizeEmail), поэтому индекс обычный, а не по LOWER(email).
        $this->addSql('CREATE UNIQUE INDEX users_email_uniq_index ON users (email)');

        $this->addSql('CREATE TABLE exercise (id VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, is_archived BOOLEAN DEFAULT FALSE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX exercise_name_uniq_index ON exercise (name)');
        $this->addSql('CREATE INDEX exercise_active_name_index ON exercise (name) WHERE is_archived = FALSE');

        $this->addSql('CREATE TABLE workout (id VARCHAR(64) NOT NULL, user_id VARCHAR(64) NOT NULL, started_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, note TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT workout_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        // Инвариант «одна незавершённая тренировка на пользователя» — физически, а не только в коде.
        $this->addSql('CREATE UNIQUE INDEX workout_active_per_user_uniq_index ON workout (user_id) WHERE finished_at IS NULL');
        $this->addSql('CREATE INDEX workout_user_started_at_index ON workout (user_id, started_at DESC)');
        // Под «последняя завершённая тренировка пользователя» (повтор тренировки и показ прошлого раза).
        $this->addSql('CREATE INDEX workout_user_finished_at_index ON workout (user_id, finished_at DESC) WHERE finished_at IS NOT NULL');
        $this->addSql('ALTER TABLE workout ADD CONSTRAINT workout_finished_after_started_check CHECK (finished_at IS NULL OR finished_at >= started_at)');

        $this->addSql('CREATE TABLE workout_exercise (id VARCHAR(64) NOT NULL, workout_id VARCHAR(64) NOT NULL, exercise_id VARCHAR(64) NOT NULL, position INT NOT NULL, note TEXT DEFAULT NULL, rest_after_exercise_seconds INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT workout_exercise_workout_id_fk FOREIGN KEY (workout_id) REFERENCES workout (id) ON DELETE CASCADE');
        // Упражнение удаляется только мягко (is_archived), поэтому физическое удаление блокируем.
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT workout_exercise_exercise_id_fk FOREIGN KEY (exercise_id) REFERENCES exercise (id) ON DELETE RESTRICT');
        // DEFERRABLE: перестановка упражнений одной транзакцией без временных значений position.
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT workout_exercise_position_uniq UNIQUE (workout_id, position) DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('CREATE INDEX workout_exercise_exercise_id_index ON workout_exercise (exercise_id)');
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT workout_exercise_position_check CHECK (position >= 0)');
        $this->addSql('ALTER TABLE workout_exercise ADD CONSTRAINT workout_exercise_rest_check CHECK (rest_after_exercise_seconds IS NULL OR rest_after_exercise_seconds >= 0)');

        // workout_set — "set" зарезервировано в SQL.
        // Поля position нет: порядок подходов = порядок создания, а id это монотонный UUIDv7.
        $this->addSql('CREATE TABLE workout_set (id VARCHAR(64) NOT NULL, workout_exercise_id VARCHAR(64) NOT NULL, weight_grams INT NOT NULL, reps SMALLINT NOT NULL, type VARCHAR(32) NOT NULL, rest_after_seconds INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE workout_set ADD CONSTRAINT workout_set_workout_exercise_id_fk FOREIGN KEY (workout_exercise_id) REFERENCES workout_exercise (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX workout_set_order_index ON workout_set (workout_exercise_id, id)');
        // weight_grams = 0 допустим: подтягивания и прочий вес тела.
        $this->addSql('ALTER TABLE workout_set ADD CONSTRAINT workout_set_weight_check CHECK (weight_grams >= 0)');
        $this->addSql('ALTER TABLE workout_set ADD CONSTRAINT workout_set_reps_check CHECK (reps > 0)');
        // При добавлении значения в SetType этот CHECK расширяется отдельной миграцией.
        $this->addSql('ALTER TABLE workout_set ADD CONSTRAINT workout_set_type_check CHECK (type IN (\'warmup\', \'working\'))');
        $this->addSql('ALTER TABLE workout_set ADD CONSTRAINT workout_set_rest_check CHECK (rest_after_seconds IS NULL OR rest_after_seconds >= 0)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on \'postgresql\'.',
        );

        $this->addSql('DROP TABLE workout_set');
        $this->addSql('DROP TABLE workout_exercise');
        $this->addSql('DROP TABLE workout');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE users');
    }
}