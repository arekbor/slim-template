<?php

declare(strict_types=1);

use App\Action\Auth\LoginAction;
use App\Action\Auth\LogoutAction;
use App\Action\Auth\RegisterAction;
use App\Action\Contact\ContactAction;
use App\Action\DashboardAction;
use App\Action\RedirectToDashboardAction;
use App\Middleware\AuthMiddleware;
use App\Middleware\NotAuthMiddleware;
use Fig\Http\Message\RequestMethodInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

return function (App $app): void {
    $app->get('/', RedirectToDashboardAction::class);

    $app
        ->get("/logout", LogoutAction::class)
        ->setName("logout");

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
    })->add(NotAuthMiddleware::class);

    $app->group('', function (RouteCollectorProxyInterface $group): void {
        $group
            ->get('/dashboard', DashboardAction::class)
            ->setName('dashboard');

        $group
            ->map(
                [RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST],
                '/contact[/{id}]',
                ContactAction::class
            )
            ->setName('contact');
    })->add(AuthMiddleware::class);
};
