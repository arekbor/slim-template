<?php

declare(strict_types=1);

use App\Repository\Migrations;
use Symfony\Component\Dotenv\Dotenv;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\ResponseEmitter;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->loadEnv(BASE_PATH . '/.env');

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAttributes(true);

$dependencies = require BASE_PATH . '/config/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

$app = AppFactory::createFromContainer($container);

$middleware = require BASE_PATH . '/config/middleware.php';
$middleware($app);

$routes = require BASE_PATH . '/config/routes.php';
$routes($app);

$container->set(RouteParserInterface::class, fn() => $app->getRouteCollector()->getRouteParser());

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$migrations = new Migrations();
$migrations->createTables();

$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
