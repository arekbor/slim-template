<?php

declare(strict_types=1);

namespace App\Action;

use App\Twig;
use DI\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class AbstractAction
{
    protected Request $request;
    protected ResponseInterface $response;

    protected array $args;

    private Twig $twig;
    private RouteParserInterface $routeParser;

    public function __invoke(
        Request $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->handleAction();
    }

    #[Inject()]
    public function setTwig(Twig $twig): void
    {
        $this->twig = $twig;
    }

    #[Inject()]
    public function setRouteParser(RouteParserInterface $routeParser): void
    {
        $this->routeParser = $routeParser;
    }

    protected function render(
        string $name,
        array $context = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        $html = $this->twig->render($name, $context);

        $response = new Response();
        $response
            ->withStatus($status)
            ->getBody()
            ->write($html)
        ;

        return $response;
    }

    protected function redirect(string $routeName): ResponseInterface
    {
        return new Response(StatusCodeInterface::STATUS_FOUND, new Headers([
            'Location' => $this->routeParser->urlFor($routeName)
        ]));
    }

    protected function hxRedirect($routeName): ResponseInterface
    {
        return new Response(StatusCodeInterface::STATUS_FOUND, new Headers([
            'HX-Location' => $this->routeParser->urlFor($routeName)
        ]));
    }

    abstract public function handleAction(): ResponseInterface;
}
