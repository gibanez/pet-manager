<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Infrastructure\Security;

use Gaby\Shared\Config\Env;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use RuntimeException;

final class JwtConfigFactory
{
    public static function create(): Configuration
    {
        $secret = Env::get('JWT_SECRET');

        if ($secret === null || $secret === '') {
            throw new RuntimeException('JWT_SECRET is not configured in .env');
        }

        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );
    }
}
