<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

return static function (
    string $projectDir,
    string $env,
    bool $debug,
    string $containerPath = '/runtime/Symfony/',
): ContainerInterface {
    $class = 'GymLog' . ucfirst($env) . ($debug ? 'Debug' : '') . 'Container';
    $cacheFile = rtrim($projectDir . $containerPath, '/') . '/' . $env . '/' . $class . '.php';

    $cache = new ConfigCache($cacheFile, $debug);

    if (!$cache->isFresh()) {
        $container = new SymfonyContainerBuilder();
        $container->setParameter('kernel.project_dir', $projectDir);
        $container->setParameter('kernel.environment', $env);
        $container->setParameter('kernel.debug', $debug);

        (new XmlFileLoader($container, new FileLocator($projectDir . '/config')))->load('services.xml');

        // Импорт services/*.xml отслеживает только те файлы, что нашлись при компиляции:
        // Config\Loader\FileLoader::import выбрасывает созданный GlobResource в переменную $_.
        // Поэтому новый файл в config/services/ кэш сам не заметит — следим за директорией явно.
        $container->addResource(new DirectoryResource($projectDir . '/config/services', '/\.xml$/'));

        // Без резолва env на этапе компиляции: %env(...)% остаются плейсхолдерами,
        // дампнутый контейнер читает их из окружения в рантайме.
        $container->compile();

        $cache->write((new PhpDumper($container))->dump(['class' => $class]), $container->getResources());
    }

    require_once $cacheFile;

    /** @var ContainerInterface */
    return new $class();
};