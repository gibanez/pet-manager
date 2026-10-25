<?php

declare(strict_types=1);

use Gaby\Apps\Api\Admin\Domain\AdminRepositoryInterface;
use Gaby\Apps\Api\Admin\Infrastructure\Persistence\SqliteAdminRepository;
use function DI\autowire;

return [
    AdminRepositoryInterface::class => autowire(SqliteAdminRepository::class),
];
