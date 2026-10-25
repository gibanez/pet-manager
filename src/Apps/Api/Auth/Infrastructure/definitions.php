<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Domain\TokenGeneratorInterface;
use Gaby\Apps\Api\Auth\Infrastructure\Security\JwtConfigFactory;
use Gaby\Apps\Api\Auth\Infrastructure\Security\JwtTokenGenerator;
use Gaby\Shared\Config\Env;
use Lcobucci\JWT\Configuration;
use function DI\factory;

return [
    Configuration::class => factory(function (): Configuration {
        return JwtConfigFactory::create();
    }),
    TokenGeneratorInterface::class => factory(function (Configuration $configuration): TokenGeneratorInterface {
        return new JwtTokenGenerator($configuration, (int) Env::get('JWT_TTL', '3600'));
    }),
];
