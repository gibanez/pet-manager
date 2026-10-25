<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\DeletePage;

final class DeletePageCommand
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
