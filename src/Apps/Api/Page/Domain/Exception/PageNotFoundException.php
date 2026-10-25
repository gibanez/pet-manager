<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Domain\Exception;

use RuntimeException;

final class PageNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Page not found: ' . $id);
    }

    public static function withSlug(string $slug): self
    {
        return new self('Page not found: ' . $slug);
    }
}
