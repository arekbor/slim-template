<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //TODO: get settings from env
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 1800,
                'domain' => 'localhost',
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            if (!@session_start()) {
                throw new \RuntimeException('Failed to start session.');
            }
        }

        $regenerationInterval = 60 * 30;
        $lastRegeneration = $_SESSION['last_regeneration'] = 0;
        if (time() - $lastRegeneration >= $regenerationInterval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }

        return $handler->handle($request);
    }
}
