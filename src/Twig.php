<?php

declare(strict_types=1);

namespace App;

use Slim\Interfaces\RouteParserInterface;
use Twig\Environment;
use Twig\TwigFunction;

final class Twig
{
    public function __construct(
        private readonly Environment $environment,
        private readonly RouteParserInterface $routeParser
    ) {
        $this->initTwigFunctions();
        $this->initTwigGlobals();
    }

    public function render(string $name, array $context = []): string
    {
        return $this->environment->render($name, $context);
    }

    private function initTwigFunctions(): void
    {
        $this->environment->addFunction(new TwigFunction('link', function (string $routeName, array $data = []): string {
            return $this->routeParser->urlFor($routeName, $data);
        }));
    }

    private function initTwigGlobals(): void
    {
        $this->environment->addGlobal('user', Session::getCurrentUser());
    }
}
