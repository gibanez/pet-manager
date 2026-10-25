<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Inquiry\Infrastructure\Http\InquiryController;
use Slim\Routing\RouteCollectorProxy;

$group->post('/inquiries', [InquiryController::class, 'create']);

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/inquiries', [InquiryController::class, 'list']);
})->add(JwtAuthMiddleware::class);
