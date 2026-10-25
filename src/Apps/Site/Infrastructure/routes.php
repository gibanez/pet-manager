<?php

declare(strict_types=1);

use Gaby\Apps\Site\Infrastructure\Http\SiteController;
use Slim\App;

$app->get('/', [SiteController::class, 'home']);
$app->get('/about', [SiteController::class, 'about']);
$app->get('/contact', [SiteController::class, 'contact']);
