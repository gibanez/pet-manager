<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Auth\Doubles;

use Gaby\Apps\Api\Auth\Domain\TokenGeneratorInterface;

final class FakeTokenGenerator implements TokenGeneratorInterface
{
    public function generate(string $subjectId, string $email): string
    {
        return 'token-' . $subjectId;
    }
}
