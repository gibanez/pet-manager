<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Domain\Exception;

use RuntimeException;

final class SlugAlreadyInUseException extends RuntimeException
{
    public static function withSlug(string $slug): self
    {
        return new self('Slug already in use: ' . $slug);
    }
}
