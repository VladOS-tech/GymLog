<?php

declare(strict_types=1);

/**
 * Этот конфиг нужен для запуска консольных команд doctrine-migrations.
 */

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

$projectRoot = $projectRoot ?? dirname(__DIR__);

$realPath = realpath($projectRoot);

require_once $realPath . '/vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'dev';
$debug = 'prod' !== getenv('APP_ENV');

/** @var ContainerInterface $container */
$container = (require __DIR__ . '/container.php')(
    $realPath,
    $env,
    $debug,
);

/** @var ConfigurationArray $migrationConfiguration */
$migrationConfiguration = $container->get('doctrine.migrations.configuration');

/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get('doctrine.orm.entitymanager');

return DependencyFactory::fromEntityManager(
    $migrationConfiguration,
    new ExistingEntityManager($entityManager),
);