<?php

declare(strict_types=1);

use App\Middleware\SessionMiddleware;
use Slim\App;

return function (App $app): void {
    $app->add(SessionMiddleware::class);
};
