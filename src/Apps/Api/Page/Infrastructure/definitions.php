<?php

declare(strict_types=1);

use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;
use Gaby\Apps\Api\Page\Infrastructure\Persistence\SqlitePageRepository;
use function DI\autowire;

return [
    PageRepositoryInterface::class => autowire(SqlitePageRepository::class),
];
