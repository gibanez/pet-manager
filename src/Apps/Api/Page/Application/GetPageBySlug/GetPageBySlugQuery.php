<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\GetPageBySlug;

final class GetPageBySlugQuery
{
    /** @var string */
    public $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }
}
