<?php

declare(strict_types=1);

use App\Action\Auth\LoginAction;
use App\Action\Auth\LogoutAction;
use App\Action\Auth\RegisterAction;
use App\Action\HomeAction;
use App\Middleware\AuthMiddleware;
use App\Middleware\HomeMiddleware;
use Fig\Http\Message\RequestMethodInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return function (App $app): void {
    $app
        ->get('/', HomeAction::class)
        ->setName('home')
        ->add(HomeMiddleware::class);

    $app->group('/auth', function (RouteCollectorProxyInterface $group): void {
        $group
            ->map(
                [RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST],
                '/login',
                LoginAction::class
            )
            ->setName("auth.login");

        $group
            ->map(
                [RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST],
                '/register',
                RegisterAction::class
            )
            ->setName("auth.register");
    })->add(AuthMiddleware::class);

    $app
        ->get("/logout", LogoutAction::class)
        ->setName("logout");
};
