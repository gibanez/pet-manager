<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\CreatePage;

use Gaby\Apps\Api\Page\Domain\Page;

final class CreatePageCommand
{
    /** @var string */
    public $slug;

    /** @var string */
    public $title;

    /** @var string */
    public $body;

    /** @var string */
    public $status;

    public function __construct(
        string $slug,
        string $title,
        string $body,
        string $status = Page::STATUS_ACTIVE
    ) {
        $this->slug = $slug;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
    }
}
