<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Auth\Domain;

interface TokenGeneratorInterface
{
    public function generate(string $subjectId, string $email): string;
}
