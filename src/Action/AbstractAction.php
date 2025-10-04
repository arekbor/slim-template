<?php

declare(strict_types=1);

namespace App\Action;

use App\Twig;
use DI\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class AbstractAction
{
    protected Request $request;
    protected ResponseInterface $response;

    private array $args;

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

    public function getArg(string $key): mixed
    {
        return isset($this->args[$key]) ? $this->args[$key] : null;
    }

    protected function render(
        string $name,
        array $context = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        $html = $this->twig->render($name, $context);

        $response = new Response();
        $response = $response->withStatus($status);
        $response->getBody()->write($html);

        return $response;
    }

    protected function redirect(string $routeName): ResponseInterface
    {
        $response = new Response();
        $response = $response->withStatus(StatusCodeInterface::STATUS_FOUND);
        $response = $response->withHeader('Location', $this->routeParser->urlFor($routeName));

        return $response;
    }

    protected function hxRedirect($routeName): ResponseInterface
    {
        $response = new Response();
        $response = $response->withStatus(StatusCodeInterface::STATUS_FOUND);
        $response = $response->withHeader('HX-Location', $this->routeParser->urlFor($routeName));

        return $response;
    }

    abstract public function handleAction(): ResponseInterface;
}
