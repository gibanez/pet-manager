<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\AuthController;

$group->post('/auth/login', [AuthController::class, 'login']);
