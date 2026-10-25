<?php

declare(strict_types=1);

use Gaby\Shared\Persistence\SqliteConnection;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use function DI\factory;

return [
    \PDO::class => factory(function (): \PDO {
        return SqliteConnection::create();
    }),
    ResponseFactoryInterface::class => factory(function (): ResponseFactoryInterface {
        return new Psr17Factory();
    }),
];
