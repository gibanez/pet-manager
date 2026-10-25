<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Gaby\Shared\Config\Env;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$timezone = Env::get('APP_TIMEZONE', 'America/Argentina/Buenos_Aires');
date_default_timezone_set($timezone === null || $timezone === '' ? 'UTC' : $timezone);

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../src/Apps/definitions.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$debug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

$errorMiddleware = $app->addErrorMiddleware($debug, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($app, $debug): ResponseInterface {
        $response = $app->getResponseFactory()->createResponse();
        $status = $exception instanceof HttpNotFoundException ? 404 : 500;
        $payload = ['error' => $status === 404 ? 'Not found.' : 'Internal server error.'];

        if ($debug) {
            $payload['detail'] = $exception->getMessage();
        }

        $response->getBody()->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
);

(require __DIR__ . '/../src/Apps/routes.php')($app);

$app->run();
