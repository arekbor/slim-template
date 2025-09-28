<?php

declare(strict_types=1);

namespace App\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

final class HomeMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RouteParserInterface $routeParser
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($_SESSION['user'])) {
            return new Response(StatusCodeInterface::STATUS_FOUND, new Headers([
                'Location' => $this->routeParser->urlFor("auth.login")
            ]));
        }

        return $handler->handle($request);
    }
}
