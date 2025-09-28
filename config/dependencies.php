<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        Environment::class => function (): Environment {
            $loader = new FilesystemLoader(BASE_PATH . '/templates');

            return new Environment($loader, [
                'cache' => false,
                'debug' => $_ENV['APP_ENV'] === 'dev'
            ]);
        }
    ]);
};
