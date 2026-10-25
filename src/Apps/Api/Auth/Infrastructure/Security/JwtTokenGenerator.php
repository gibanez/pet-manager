<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Infrastructure\Security;

use DateTimeImmutable;
use Gaby\Apps\Api\Auth\Domain\TokenGeneratorInterface;
use Lcobucci\JWT\Configuration;

final class JwtTokenGenerator implements TokenGeneratorInterface
{
    /** @var Configuration */
    private $configuration;

    /** @var int */
    private $ttl;

    public function __construct(Configuration $configuration, int $ttl)
    {
        $this->configuration = $configuration;
        $this->ttl = $ttl > 0 ? $ttl : 3600;
    }

    public function generate(string $subjectId, string $email): string
    {
        $now = new DateTimeImmutable();

        $token = $this->configuration->builder()
            ->issuedBy('php-ddd-base')
            ->permittedFor('php-ddd-base')
            ->relatedTo($subjectId)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+' . $this->ttl . ' seconds'))
            ->withClaim('email', $email)
            ->getToken($this->configuration->signer(), $this->configuration->signingKey());

        return $token->toString();
    }
}
