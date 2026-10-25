<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\UpdatePage;

final class UpdatePageCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $slug;

    /** @var string */
    public $title;

    /** @var string */
    public $body;

    /** @var string */
    public $status;

    public function __construct(string $id, string $slug, string $title, string $body, string $status)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
    }
}
