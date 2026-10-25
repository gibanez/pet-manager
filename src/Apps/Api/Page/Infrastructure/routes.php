<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Page\Infrastructure\Http\PageController;
use Slim\Routing\RouteCollectorProxy;

$group->get('/public/pages/{slug}', [PageController::class, 'showBySlug']);

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/pages', [PageController::class, 'list']);
    $writeGroup->get('/pages/{id}', [PageController::class, 'show']);
    $writeGroup->post('/pages', [PageController::class, 'create']);
    $writeGroup->put('/pages/{id}', [PageController::class, 'update']);
    $writeGroup->delete('/pages/{id}', [PageController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
